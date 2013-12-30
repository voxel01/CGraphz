<?php
namespace Core\Model;

use Zend\Config\Config;

define('PREG_FIND_RECURSIVE', 1);
define('PREG_FIND_DIRMATCH', 2);
define('PREG_FIND_FULLPATH', 4);
define('PREG_FIND_NEGATE', 8);
define('PREG_FIND_DIRONLY', 16);
define('PREG_FIND_RETURNASSOC', 32);
define('PREG_FIND_SORTDESC', 64);
define('PREG_FIND_SORTKEYS', 128);
define('PREG_FIND_SORTBASENAME', 256);   # requires PREG_FIND_RETURNASSOC
define('PREG_FIND_SORTMODIFIED', 512);   # requires PREG_FIND_RETURNASSOC
define('PREG_FIND_SORTFILESIZE', 1024);  # requires PREG_FIND_RETURNASSOC
define('PREG_FIND_SORTDISKUSAGE', 2048); # requires PREG_FIND_RETURNASSOC
define('PREG_FIND_SORTEXTENSION', 4096); # requires PREG_FIND_RETURNASSOC
define('PREG_FIND_FOLLOWSYMLINKS', 8192);
class PluginFilter
{
    /**
     * @var Config
     */
    protected $config;
    protected $filter;
    /**
     * @var Server
     */
    protected $server;

    public function setConfig(Config $c)
    {
        $this->config = $c;
    }

    public function setFilter(array $f)
    {
        $this->filter = $f;
    }

    public function setServer(Server $s)
    {
        $this->server = $s;
    }

    public function getPlugins()
    {
        $plugs=array();
        //var_dump($this->config->datadir.DIRECTORY_SEPARATOR.$this->server->server_name.DIRECTORY_SEPARATOR);exit();
        if (is_dir($this->config->datadir.DIRECTORY_SEPARATOR.$this->server->server_name.DIRECTORY_SEPARATOR)) {
            foreach ($this->filter as $filter) {
                $myregex='#^('.$this->config->datadir.DIRECTORY_SEPARATOR.$this->server->server_name.DIRECTORY_SEPARATOR.')('.$filter->plugin.')(?:\-('.$filter->plugin_instance.'))?/('.$filter->type.')(?:\-('.$filter->type_instance.'))?\.rrd#';
                //echo $myregex; exit();
                $plugins = $this->preg_find($myregex, $this->config->datadir.DIRECTORY_SEPARATOR.$this->server->server_name, PREG_FIND_RECURSIVE|PREG_FIND_FULLPATH|PREG_FIND_SORTBASENAME);
                foreach ($plugins as $plugin) {
                    preg_match($myregex, $plugin, $matches);
                    $pluginProcessed = new Plugin();

                    if (isset($matches[2])) {
                        $pluginProcessed->plugin = $matches[2];
                        if (!isset(${$pluginProcessed->plugin})) ${$pluginProcessed->plugin}=false;
                    } else {
                        $pluginProcessed->plugin = null;
                    }
                    if (isset($matches[3])) {
                        $pluginProcessed->pluginInstance=$matches[3];
                        $pluginProcessed->pluginCategory = null;
                        if (substr_count($pluginProcessed->pluginInstance, '-') >= 1 && preg_match($this->config->plugin_pcategory, $pluginProcessed->plugin)) {
                            $tmp=explode('-',$pluginProcessed->pluginInstance);
                            // Fix when PI is null after separating PC/PI for example a directory named "MyHost/GenericJMX-cassandra_activity_request-/"
                            if (strlen($tmp[1])) {
                                $pluginProcessed->pluginCategory = $tmp[0];
                                $pluginProcessed->pluginInstance = implode('-', array_slice($tmp,1));
                            }
                            // Copy PI to PC if no PC but Plugin can have a PC
                        } else if (preg_match($this->config->plugin_pcategory, $pluginProcessed->plugin)) {
                            $pluginProcessed->pluginCategory = $pluginProcessed->pluginInstance;
                            $pluginProcessed->pluginInstance = null;
                        }
                    } else {
                        $pluginProcessed->pluginCategory = null;
                        $pluginProcessed->pluginInstance = null;
                    }
                    if (isset($matches[4])) {
                        $pluginProcessed->type = $matches[4];
                    } else {
                        $pluginProcessed->type =null;
                    }
                    if (isset($matches[5])) {
                        $pluginProcessed->typeInstance = $matches[5];
                        $pluginProcessed->typeCategory = null;
                        if (substr_count($pluginProcessed->typeInstance, '-') >= 1 && preg_match($this->config->collectd->plugin_tcategory, $pluginProcessed->plugin)) {
                            $tmp=explode('-',$pluginProcessed->typeInstance);
                            $pluginProcessed->typeCategory=$tmp[0];
                            //$ti=implode('-', array_slice($tmp,1));
                            $pluginProcessed->typeInstance=null;
                        } else if (preg_match($this->config->plugin_tcategory, $pluginProcessed->plugin)) {
                            $pluginProcessed->typeCategory=$pluginProcessed->typeInstance;
                            $pluginProcessed->typeInstance=null;
                        }
                        $pluginProcessed->typeInstance=null;
                    } else {
                        $pluginProcessed->typeCategory=null;
                        $pluginProcessed->typeInstance=null;
                    }
                    if(!in_array($pluginProcessed,$plugs))
                    {
                        $plugs[] = $pluginProcessed;
                    }
                }
            }
        }
        return $plugs;
    }

    public function preg_find($pattern, $start_dir='.', $args=NULL) {

        static $depth = -1;
        ++$depth;

        $files_matched = array();

        $fh = opendir($start_dir);

        while (($file = readdir($fh)) !== false) {
            if (strcmp($file, '.')==0 || strcmp($file, '..')==0) continue;
            $filepath = $start_dir . '/' . $file;
            if (preg_match($pattern,
                ($args & PREG_FIND_FULLPATH) ? $filepath : $file)) {
                $doadd =    is_file($filepath)
                    || (is_dir($filepath) && ($args & PREG_FIND_DIRMATCH))
                    || (is_dir($filepath) && ($args & PREG_FIND_DIRONLY));
                if ($args & PREG_FIND_DIRONLY && $doadd && !is_dir($filepath)) $doadd = false;
                if ($args & PREG_FIND_NEGATE) $doadd = !$doadd;
                if ($doadd) {
                    if ($args & PREG_FIND_RETURNASSOC) { // return more than just the filenames
                        $fileres = array();
                        if (function_exists('stat')) {
                            $fileres['stat'] = stat($filepath);
                            $fileres['du'] = $fileres['stat']['blocks'] * 512;
                        }
                        if (function_exists('fileowner')) $fileres['uid'] = fileowner($filepath);
                        if (function_exists('filegroup')) $fileres['gid'] = filegroup($filepath);
                        if (function_exists('filetype')) $fileres['filetype'] = filetype($filepath);
                        if (function_exists('mime_content_type')) $fileres['mimetype'] = mime_content_type($filepath);
                        if (function_exists('dirname')) $fileres['dirname'] = dirname($filepath);
                        if (function_exists('basename')) $fileres['basename'] = basename($filepath);
                        if (($i=strrpos($fileres['basename'], '.'))!==false) $fileres['ext'] = substr($fileres['basename'], $i+1); else $fileres['ext'] = '';
                        if (isset($fileres['uid']) && function_exists('posix_getpwuid')) $fileres['owner'] = posix_getpwuid ($fileres['uid']);
                        $files_matched[$filepath] = $fileres;
                    } else
                        array_push($files_matched, $filepath);
                }
            }
            if ( is_dir($filepath) && ($args & PREG_FIND_RECURSIVE) ) {
                if (!is_link($filepath) || ($args & PREG_FIND_FOLLOWSYMLINKS))
                    $files_matched = array_merge($files_matched,
                        $this->preg_find($pattern, $filepath, $args));
            }
        }

        closedir($fh);

        // Before returning check if we need to sort the results.
        if (($depth==0) && ($args & (PREG_FIND_SORTKEYS|PREG_FIND_SORTBASENAME|PREG_FIND_SORTMODIFIED|PREG_FIND_SORTFILESIZE|PREG_FIND_SORTDISKUSAGE)) ) {
            $order = ($args & PREG_FIND_SORTDESC) ? 1 : -1;
            $sortby = '';
            if ($args & PREG_FIND_RETURNASSOC) {
                if ($args & PREG_FIND_SORTMODIFIED)  $sortby = "['stat']['mtime']";
                if ($args & PREG_FIND_SORTBASENAME)  $sortby = "['basename']";
                if ($args & PREG_FIND_SORTFILESIZE)  $sortby = "['stat']['size']";
                if ($args & PREG_FIND_SORTDISKUSAGE) $sortby = "['du']";
                if ($args & PREG_FIND_SORTEXTENSION) $sortby = "['ext']";
            }
            $filesort = create_function('$a,$b', "\$a1=\$a$sortby;\$b1=\$b$sortby; if (\$a1==\$b1) return 0; else return (\$a1<\$b1) ? $order : 0- $order;");
            uasort($files_matched, $filesort);
        }
        --$depth;
        return $files_matched;

    }
}
