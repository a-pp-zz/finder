<?php
namespace AppZz\Filesystem\Finder;
use AppZz\Filesystem\Finder;
use AppZz\Helpers\Arr;
use \DateTimeZone;
use \DateTime;

class Result {

    protected $_rit;
    protected $_checked_method = 'isFile';

    public function __construct ($rit = NULL)
    {
        $this->_rit = $rit;
        $type       = Arr::get(Finder::$filter, 'type', 'file');
        $this->_checked_method = 'is'.mb_convert_case($type, MB_CASE_TITLE);
    }

    /**
     * Get files
     * @param array $attrs
     * @return array|bool
     */
    public function get_files (array $attrs = ['mtime', 'atime', 'size', 'owner', 'group'], $extra = [])
    {
        if ( ! empty ($this->_rit))
        {
            $files = [];
            $extra = (array) $extra;

            $timezone = Arr::get ($extra, 'timezone', 'Europe/Moscow');
            $format = Arr::get ($extra, 'format', 'Y-m-d H:i:s');

            foreach ($this->_rit as $filePath => $fileInfo)
            {

                $checked = call_user_func (array (&$fileInfo, $this->_checked_method));

                if ($checked)
                {
                    if (empty ($attrs))
                    {
                        $files[] = $fileInfo->isLink () ? $fileInfo->getLinkTarget () : $fileInfo->getRealPath ();
                    }
                    else
                    {
                        $obj = [];
                        $obj['path'] = $fileInfo->getRealPath ();

                        if ($fileInfo->isLink ()) {
                            $obj['link'] = $fileInfo->getLinkTarget ();                            
                        }

                        foreach ($attrs as $attr)
                        {
                            $attr = trim ($attr);
                            $method = 'get' . mb_convert_case($attr, MB_CASE_TITLE);

                            if (method_exists ($fileInfo, $method))
                            {
                                $value = call_user_func (array (&$fileInfo, $method));
                            }
                            else
                            {
                                continue;
                            }

                            switch ($attr)
                            {
                                case 'owner':
                                    $value = posix_getpwuid ($value);
                                    $obj['owner'] = Arr::get ($value, 'name');
                                    $obj['uid'] = Arr::get ($value, 'uid');
                                    $obj['gid'] = Arr::get ($value, 'gid');
                                    $value = NULL;
                                break;

                                case 'group':
                                    $value = posix_getgrgid ($value);
                                break;

                                case 'perms':
                                    $value = substr (sprintf ('%o', $value), -4);
                                break;
                            }

                            if ($value) {
                                $obj[$attr] = $value;

                                if ( ! empty ($timezone) AND ! empty ($format) AND in_array($attr, ['atime', 'mtime'])) {
                                    $obj[$attr . '_formatted'] = Result::_format_date($value, $timezone, $format);
                                } elseif ($attr == 'size') {
                                    $obj[$attr . '_formatted'] = Result::_human_filesize($value);
                                }
                            }
                        }

                        $files[] = $obj;
                        unset ($obj);
                    }
                }
            }

            return $files;
        }

        return FALSE;
    }

    private static function _format_date ($ts, $timezone, $format)
    {
        $tz = new DateTimeZone ($timezone);
        $dt = new DateTime (NULL, $tz);
        $dt->setTimestamp ($ts);
        return $dt->format ($format);
    }

    private static function _human_filesize ($bytes, $decimals = 2)
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $factor = floor(log($bytes, 1024));
        return sprintf("%.{$decimals}f ", $bytes / pow(1024, $factor)) . ['B', 'KB', 'MB', 'GB', 'TB', 'PB'][$factor];
    }
}
