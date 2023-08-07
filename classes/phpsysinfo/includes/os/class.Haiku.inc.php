<?php
/**
 * Haiku System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI Haiku OS class
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2012 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.Haiku.inc.php 687 2012-09-06 20:54:49Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * Haiku sysinfo class
 * get all the required information from Haiku system
 *
 * @category  PHP
 * @package   PSI Haiku OS class
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2012 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Haiku extends OS
{
    /**
     * call parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * get the cpu information
     *
     * @return array
     */
    protected function _cpuinfo()
    {

        if (CommonFunctions::executeProgram('sysinfo', '-cpu', $bufr, PSI_DEBUG)) {
            $cpus = preg_split("/\nCPU #\d+/", (string) $bufr, -1, PREG_SPLIT_NO_EMPTY);
            $cpuspeed = "";
            foreach ($cpus as $cpu) {
                if (preg_match("/^.*running at (\d+)MHz/", (string) $cpu, $ar_buf)) {
                    $cpuspeed = $ar_buf[1];
                } elseif (preg_match("/^: \"(.*)\"/", (string) $cpu, $ar_buf)) {
                    $dev = new CpuDevice();
                    $dev->setModel($ar_buf[1]);
                    $arrLines = preg_split("/\n/", (string) $cpu, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($arrLines as $Line) {
                      if (preg_match("/^\s+Data TLB:\s+(.*)K-byte/", (string) $Line, $Line_buf)) {
                        $dev->setCache(max($Line_buf[1]*1024,$dev->getCache()));
                      } elseif (preg_match("/^\s+Data TLB:\s+(.*)M-byte/", (string) $Line, $Line_buf)) {
                        $dev->setCache(max($Line_buf[1]*1024*1024,$dev->getCache()));
                      } elseif (preg_match("/^\s+Data TLB:\s+(.*)G-byte/", (string) $Line, $Line_buf)) {
                        $dev->setCache(max($Line_buf[1]*1024*1024*1024,$dev->getCache()));
                      } elseif (preg_match("/\s+VMX/", (string) $Line, $Line_buf)) {
                        $dev->setVirt("vmx");
                      } elseif (preg_match("/\s+SVM/", (string) $Line, $Line_buf)) {
                        $dev->setVirt("svm");
                      }
                    }
                    if ($cpuspeed != "" )$dev->setCpuSpeed($cpuspeed);
                    $this->sys->setCpus($dev);
                  //echo ">>>>>".$cpu;
                }
            }
        }
    }

    /**
     * PCI devices
     * get the pci device information
     *
     * @return void
     */
    protected function _pci()
    {
        if (CommonFunctions::executeProgram('listdev', '', $bufr, PSI_DEBUG)) {
//            $devices = preg_split("/^device |\ndevice /", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            $devices = preg_split("/^device /m", (string) $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($devices as $device) {
                $ar_buf = preg_split("/\n/", (string) $device);
                if ((is_countable($ar_buf) ? count($ar_buf) : 0) >= 3) {
                    if (preg_match("/^([^\(\[\n]*)/", (string) $device, $ar_buf2)) {
                        if (preg_match("/^[^\(]*\((.*)\)/", (string) $device, $ar_buf3)) {
                            $ar_buf2[1] = $ar_buf3[1];
                        }
                        $name = trim($ar_buf2[1]).": ";

                        if (preg_match("/^\s+vendor\s+[0-9a-fA-F]{4}:\s+(.*)/", $ar_buf[1], $ar_buf3)) {
                            $name .=$ar_buf3[1]." ";
                        }
                        if (preg_match("/^\s+device\s+[0-9a-fA-F]{4}:\s+(.*)/", $ar_buf[2], $ar_buf3)) {
                            $name .=$ar_buf3[1]." ";
                        }
                        $dev = new HWDevice();
                        $dev->setName(trim($name));
                        $this->sys->setPciDevices($dev);
                    }
                }
            }
        }
    }

    /**
     * USB devices
     * get the usb device information
     *
     * @return void
     */
    protected function _usb()
    {
        if (CommonFunctions::executeProgram('listusb', '', $bufr, PSI_DEBUG)) {
            $devices = preg_split("/\n/", (string) $bufr);
            foreach ($devices as $device) {
                if (preg_match("/^\S+\s+\S+\s+\"(.*)\"\s+\"(.*)\"/", (string) $device, $ar_buf)) {
                    $dev = new HWDevice();
                    $dev->setName(trim($ar_buf[1]." ".$ar_buf[2]));
                    $this->sys->setUSBDevices($dev);
                }
            }
        }
    }

    /**
     * Haiku Version
     *
     * @return void
     */
    private function _kernel()
    {
        if (CommonFunctions::executeProgram('uname', '-rvm', $ret)) {
               $this->sys->setKernel($ret);
        }
    }

    /**
     * Distribution
     *
     * @return void
     */
    protected function _distro()
    {
        if (CommonFunctions::executeProgram('uname', '-sr', $ret))
            $this->sys->setDistribution($ret);
        else
            $this->sys->setDistribution('Haiku');

        $this->sys->setDistributionIcon('Haiku.png');
    }

    /**
     * UpTime
     * time the system is running
     *
     * @return void
     */
    private function _uptime()
    {
        if (CommonFunctions::executeProgram('uptime', '-u', $buf)) {
            if (preg_match("/^up (\d+) minute[s]?/", (string) $buf, $ar_buf)) {
                $min = $ar_buf[1];
                $this->sys->setUptime($min * 60);
            } elseif (preg_match("/^up (\d+) hour[s]?, (\d+) minute[s]?/", (string) $buf, $ar_buf)) {
                $min = $ar_buf[2];
                $hours = $ar_buf[1];
                $this->sys->setUptime($hours * 3600 + $min * 60);
            } elseif (preg_match("/^up (\d+) day[s]?, (\d+) hour[s]?, (\d+) minute[s]?/", (string) $buf, $ar_buf)) {
                $min = $ar_buf[3];
                $hours = $ar_buf[2];
                $days = $ar_buf[1];
                $this->sys->setUptime($days * 86400 + $hours * 3600 + $min * 60);
            }
        }
    }

    /**
     * Processor Load
     * optionally create a loadbar
     *
     * @return void
     */
    private function _loadavg()
    {
        if (CommonFunctions::executeProgram('top', '-n 1 -i 1', $buf)) {
            if (preg_match("/\s+(\S+)%\s+TOTAL\s+\(\S+%\s+idle time/", (string) $buf, $ar_buf)) {
                $this->sys->setLoad($ar_buf[1]);
                if (PSI_LOAD_BAR) {
                    $this->sys->setLoadPercent(round($ar_buf[1]));
                }
            }
        }
    }

    /**
     * Number of Users
     *
     * @return void
     */
    private function _users()
    {
        $this->sys->setUsers(1);
    }

    /**
     * Virtual Host Name
     *
     * @return void
     */
    private function _hostname()
    {
        if (PSI_USE_VHOST === true) {
            $this->sys->setHostname(getenv('SERVER_NAME'));
        } else {
            if (CommonFunctions::executeProgram('uname', '-n', $result, PSI_DEBUG)) {
                $ip = gethostbyname($result);
                if ($ip != $result) {
                    $this->sys->setHostname(gethostbyaddr($ip));
                }
            }
        }
    }

    /**
     * IP of the Virtual Host Name
     *
     *  @return void
     */
    private function _ip()
    {
        if (PSI_USE_VHOST === true) {
            $this->sys->setIp(gethostbyname($this->sys->getHostname()));
        } else {
            if (!($result = getenv('SERVER_ADDR'))) {
                $this->sys->setIp(gethostbyname($this->sys->getHostname()));
            } else {
                $this->sys->setIp($result);
            }
        }
    }

    /**
     *  Physical memory information and Swap Space information
     *
     *  @return void
     */
    private function _memory()
    {
        if (CommonFunctions::executeProgram('sysinfo', '-mem', $bufr, PSI_DEBUG)) {
            if (preg_match("/(.*)bytes free\s+\(used\/max\s+(.*)\s+\/\s+(.*)\)\s*\n\s+\(cached\s+(.*)\)/", (string) $bufr, $ar_buf)) {
                $this->sys->setMemTotal($ar_buf[3]);
                $this->sys->setMemFree($ar_buf[1]);
                $this->sys->setMemCache($ar_buf[4]);
                $this->sys->setMemUsed($ar_buf[2]);
            }
        }
        if (CommonFunctions::executeProgram('vmstat', '', $bufr, PSI_DEBUG)) {
            if (preg_match("/max swap space:\s+(.*)\nfree swap space:\s+(.*)\n/", (string) $bufr, $ar_buf)) {
                if ($ar_buf[1]>0) {
                    $dev = new DiskDevice();
                    $dev->setMountPoint("/boot/common/var/swap");
                    $dev->setName("SWAP");
                    $dev->setTotal($ar_buf[1]);
                    $dev->setFree($ar_buf[2]);
                    $dev->setUSed($ar_buf[1]-$ar_buf[2]);
                    $this->sys->setSwapDevices($dev);
                }
            }
        }
    }

    /**
     * filesystem information
     *
     * @return void
     */
    private function _filesystems()
    {
      if (CommonFunctions::executeProgram('df', '-b', $df, PSI_DEBUG)) {
          $df = preg_split("/\n/", (string) $df, -1, PREG_SPLIT_NO_EMPTY);
          foreach ($df as $df_line) {
              $ar_buf = preg_split("/\s+/", (string) $df_line);
              if ((str_starts_with((string) $df_line, "/")) && ((is_countable($ar_buf) ? count($ar_buf) : 0) == 6 )) {
                  $dev = new DiskDevice();
                  $dev->setMountPoint($ar_buf[0]);
                  $dev->setName($ar_buf[5]);
                  $dev->setFsType($ar_buf[1]);
                  $dev->setOptions($ar_buf[4]);
                  $dev->setTotal($ar_buf[2] * 1024);
                  $dev->setFree($ar_buf[3] * 1024);
                  $dev->setUsed($dev->getTotal() - $dev->getFree());
                  $this->sys->setDiskDevices($dev);
             }
          }
      }
    }

    /**
     * network information
     *
     * @return void
     */
    private function _network()
    {
        $dev = null;
        $errors = null;
        $drops = null;
        if (CommonFunctions::executeProgram('ifconfig', '', $bufr, PSI_DEBUG)) {
            $lines = preg_split("/\n/", (string) $bufr, -1, PREG_SPLIT_NO_EMPTY);
            $notwas = true;
            foreach ($lines as $line) {
                if (preg_match("/^(\S+)/", (string) $line, $ar_buf)) {
                    if (!$notwas) {
                        $dev->setErrors($errors);
                        $dev->setDrops($drops);
                        $this->sys->setNetDevices($dev);
                    }
                    $errors = 0;
                    $drops = 0;
                    $dev = new NetDevice();
                    $dev->setName($ar_buf[1]);
                    $notwas = false;
                } else {
                    if (!$notwas) {
                        if (preg_match('/\sReceive:\s\d+\spackets,\s(\d+)\serrors,\s(\d+)\sbytes,\s\d+\smcasts,\s(\d+)\sdropped/i', (string) $line, $ar_buf2)) {
                            $errors +=$ar_buf2[1];
                            $drops +=$ar_buf2[3];
                            $dev->setRxBytes($ar_buf2[2]);
                        } elseif (preg_match('/\sTransmit:\s\d+\spackets,\s(\d+)\serrors,\s(\d+)\sbytes,\s\d+\smcasts,\s(\d+)\sdropped/i', (string) $line, $ar_buf2)) {
                            $errors +=$ar_buf2[1];
                            $drops +=$ar_buf2[3];
                            $dev->setTxBytes($ar_buf2[2]);
                        }

                        if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                            if (preg_match('/\sEthernet,\s+Address:\s(\S*)/i', (string) $line, $ar_buf2))
                                    $dev->setInfo(preg_replace('/:/', '-', $ar_buf2[1]));
                            elseif (preg_match('/^\s+inet\saddr:\s(\S*),/i', (string) $line, $ar_buf2))
                                     $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                                 elseif (preg_match('/^\s+inet6\saddr:\s(\S*),/i', (string) $line, $ar_buf2))
                                          if (!preg_match('/^fe80::/i',$ar_buf2[1]))
                                            $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                        }
                    }
                }
            }
            if (!$notwas) {
                $dev->setErrors($errors);
                $dev->setDrops($drops);
                $this->sys->setNetDevices($dev);
            }
        }
    }

    /**
     * get the information
     *
     * @return Void
     */
    public function build()
    {
        $this->error->addError("WARN", "The Haiku version of phpSysInfo is a work in progress, some things currently don't work");
        $this->_hostname();
        $this->_ip();
        $this->_distro();
        $this->_kernel();
        $this->_uptime();
        $this->_users();
        $this->_loadavg();
        $this->_pci();
        $this->_usb();
        $this->_cpuinfo();
        $this->_memory();
        $this->_filesystems();
        $this->_network();
    }
}
