<?php
/**
 * DMRaid Plugin
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_DMRaid
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.dmraid.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * mdstat Plugin, which displays a snapshot of the kernel's RAID/md state
 * a simple view which shows supported types and RAID-Devices which are determined by
 * parsing the "/proc/mdstat" file, another way is to provide
 * a file with the output of the /proc/mdstat file, so there is no need to run a execute by the
 * webserver, the format of the command is written down in the mdstat.config.php file, where also
 * the method of getting the information is configured
 *
 * @category  PHP
 * @package   PSI_Plugin_DMRaid
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class DMRaid extends PSI_Plugin
{
    /**
     * variable, which holds the content of the command
     * @var array
     */
    private $_filecontent = "";

    /**
     * variable, which holds the result before the xml is generated out of this array
     */
    private array $_result = [];

    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc encoding
     */
    public function __construct($enc)
    {
        $buffer = "";
        parent::__construct(self::class, $enc);
        match (strtolower((string) PSI_PLUGIN_DMRAID_ACCESS)) {
            'command' => CommonFunctions::executeProgram("dmraid", "-s -vv 2>&1", $buffer),
            'data' => CommonFunctions::rfts(APP_ROOT."/data/dmraid.txt", $buffer),
            default => $this->global_error->addConfigError("__construct()", "PSI_PLUGIN_DMRAID_ACCESS"),
        };
        if (trim((string) $buffer) != "") {
            $this->_filecontent = preg_split("/(\n\*\*\* )|(\n--> )/", (string) $buffer, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $this->_filecontent = [];
        }
    }

    /**
     * doing all tasks to get the required informations that the plugin needs
     * result is stored in an internal array<br>the array is build like a tree,
     * so that it is possible to get only a specific process with the childs
     *
     * @return void
     */
    public function execute()
    {
        if ( empty($this->_filecontent)) {
            return;
        }
        $group = "";
        foreach ($this->_filecontent as $block) {
            if (preg_match('/^(NOTICE: )|(ERROR: )/m', (string) $block)) {
                $group = "";
                $lines = preg_split("/\n/", (string) $block, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($lines as $line) {
                    if (preg_match('/^NOTICE: added\s+\/dev\/(.+)\s+to RAID set\s+\"(.+)\"/', (string) $line, $partition)) {
                        $this->_result['devices'][$partition[2]]['partitions'][$partition[1]]['status'] = "";
                    } elseif (preg_match('/^ERROR: .* device\s+\/dev\/(.+)\s+(.+)\s+in RAID set\s+\"(.+)\"/', (string) $line, $partition)) {
                        if ($partition[2]=="broken") {
                            $this->_result['devices'][$partition[3]]['partitions'][$partition[1]]['status'] = 'F';
                        }
                    }
                }
            } else {
                if (preg_match('/^Group superset\s+(.+)/m', (string) $block, $arrname)) {
                    $group = $arrname[1];
                }
                if (preg_match('/^name\s*:\s*(.*)/m', (string) $block, $arrname)) {
                    if ($group=="") {
                        $group = $arrname[1];
                    }
                    $this->_result['devices'][$group]['name'] = $arrname[1];
                    if (preg_match('/^size\s*:\s*(.*)/m', (string) $block, $size)) {
                        $this->_result['devices'][$group]['size'] = $size[1];
                    }
                    if (preg_match('/^stride\s*:\s*(.*)/m', (string) $block, $stride)) {
                            $this->_result['devices'][$group]['stride'] = $stride[1];
                    }
                    if (preg_match('/^type\s*:\s*(.*)/m', (string) $block, $type)) {
                        $this->_result['devices'][$group]['type'] = $type[1];
                    }
                    if (preg_match('/^status\s*:\s*(.*)/m', (string) $block, $status)) {
                        $this->_result['devices'][$group]['status'] = $status[1];
                    }
                    if (preg_match('/^subsets\s*:\s*(.*)/m', (string) $block, $subsets)) {
                        $this->_result['devices'][$group]['subsets'] = $subsets[1];
                    }
                    if (preg_match('/^devs\s*:\s*(.*)/m', (string) $block, $devs)) {
                        $this->_result['devices'][$group]['devs'] = $devs[1];
                    }
                    if (preg_match('/^spares\s*:\s*(.*)/m', (string) $block, $spares)) {
                            $this->_result['devices'][$group]['spares'] = $spares[1];
                    }
                    $group = "";
                }
            }
        }
    }

    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLObject entire XML content for the plugin
     */
    public function xml()
    {
        if ( empty($this->_result)) {
            return $this->xml->getSimpleXmlElement();
        }
        foreach ($this->_result['devices'] as $key=>$device) {
            $dev = $this->xml->addChild("Raid");
            $dev->addAttribute("Device_Name", $key);
            $dev->addAttribute("Type", $device["type"]);
            $dev->addAttribute("Disk_Status", $device["status"]);
            $dev->addAttribute("Name", $device["name"]);
            $dev->addAttribute("Size", $device["size"]);
            $dev->addAttribute("Stride", $device["stride"]);
            $dev->addAttribute("Subsets", $device["subsets"]);
            $dev->addAttribute("Devs", $device["devs"]);
            $dev->addAttribute("Spares", $device["spares"]);
            $disks = $dev->addChild("Disks");
            if (isset($device['partitions']) && sizeof($device['partitions']>0)) foreach ($device['partitions'] as $diskkey=>$disk) {
                $disktemp = $disks->addChild("Disk");
                $disktemp->addAttribute("Name", $diskkey);
                $disktemp->addAttribute("Status", $disk['status']);
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
