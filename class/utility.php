<?php

/**
 * Class TdmmoneyUtility
 */
class TdmmoneyUtility
{
    /**
     * Check Xoops Version against a provided version
     *
     * @param int $x
     * @param int $y
     * @param int $z
     * @param string $signal
     * @return bool
     */
    public static function checkXoopsVersion($x, $y, $z, $signal = '==')
    {
        $xv = explode('-', str_replace('XOOPS ', '', XOOPS_VERSION));

        list($a, $b, $c) = explode('.', $xv[0]);
        $xv = $a*10000 + $b*100 + $c;
        $mv = $x*10000 + $y*100 + $z;
        if ($signal === '>') {
            return $xv > $mv;
        }
        if ($signal === '>=') {
            return $xv >= $mv;
        }
        if ($signal === '<') {
            return $xv < $mv;
        }
        if ($signal === '<=') {
            return $xv <= $mv;
        }
        if ($signal === '==') {
            return $xv == $mv;
        }

        return false;
    }
    /**
     * Function responsible for checking if a directory exists, we can also write in and create an index.html file
     *
     * @param string $folder The full path of the directory to check
     *
     * @return void
     */
    public static function createFolder($folder)
    {
        //        try {
//            if (!mkdir($folder) && !is_dir($folder)) {
//                throw new \RuntimeException(sprintf('Unable to create the %s directory', $folder));
//            } else {
//                file_put_contents($folder . '/index.html', '<script>history.go(-1);</script>');
//            }
//        }
//        catch (Exception $e) {
//            echo 'Caught exception: ', $e->getMessage(), "\n", '<br/>';
//        }
        try {
            if (!file_exists($folder)) {
                if (!mkdir($folder) && !is_dir($folder)) {
                    throw new \RuntimeException(sprintf('Unable to create the %s directory', $folder));
                } else {
                    file_put_contents($folder . '/index.html', '<script>history.go(-1);</script>');
                }
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n", '<br/>';
        }
    }

    /**
     * @param $file
     * @param $folder
     * @return bool
     */
    public static function copyFile($file, $folder)
    {
        return copy($file, $folder);
        //        try {
        //            if (!is_dir($folder)) {
        //                throw new \RuntimeException(sprintf('Unable to copy file as: %s ', $folder));
        //            } else {
        //                return copy($file, $folder);
        //            }
        //        } catch (Exception $e) {
        //            echo 'Caught exception: ', $e->getMessage(), "\n", "<br/>";
        //        }
        //        return false;
    }

    /**
     * @param $src
     * @param $dst
     */
    public static function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        //    @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     *
     * Verifies XOOPS version meets minimum requirements for this module
     * @static
     * @param XoopsModule $module
     *
     * @return bool true if meets requirements, false if not
     */
    public static function checkVerXoops(XoopsModule $module)
    {
        xoops_loadLanguage('admin', $module->dirname());
        //check for minimum XOOPS version
        $currentVer  = substr(XOOPS_VERSION, 6); // get the numeric part of string
        $currArray   = explode('.', $currentVer);
        $requiredVer = '' . $module->getInfo('min_xoops'); //making sure it's a string
        $reqArray    = explode('.', $requiredVer);
        $success     = true;
        foreach ($reqArray as $k => $v) {
            if (isset($currArray[$k])) {
                if ($currArray[$k] > $v) {
                    break;
                } elseif ($currArray[$k] == $v) {
                    continue;
                } else {
                    $success = false;
                    break;
                }
            } else {
                if ((int)$v > 0) { // handles things like x.x.x.0_RC2
                    $success = false;
                    break;
                }
            }
        }

        if (!$success) {
            $module->setErrors(sprintf(_AM_TDMMONEY_ERROR_BAD_XOOPS, $requiredVer, $currentVer));
        }

        return $success;
    }

    /**
     *
     * Verifies PHP version meets minimum requirements for this module
     * @static
     * @param XoopsModule $module
     *
     * @return bool true if meets requirements, false if not
     */
    public static function checkVerPhp(XoopsModule $module)
    {
        xoops_loadLanguage('admin', $module->dirname());
        // check for minimum PHP version
        $success = true;
        $verNum  = PHP_VERSION;
        $reqVer  =& $module->getInfo('min_php');
        if (false !== $reqVer && '' !== $reqVer) {
            if (version_compare($verNum, $reqVer, '<')) {
                $module->setErrors(sprintf(_AM_TDMMONEY_ERROR_BAD_PHP, $reqVer, $verNum));
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param $permtype
     * @param $dirname
     * @return mixed
     */
    public static function getMygetItemIds($permtype, $dirname)
    {
        global $xoopsUser;
        /* @var $moduleHandler XoopsModuleHandler  */
        $moduleHandler = xoops_getHandler('module');
        $tdmModule     = $moduleHandler->getByDirname($dirname);
        $groups        = is_object($xoopsUser) ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
        /* @var $gpermHandler XoopsGroupPermHandler  */
        $gpermHandler  = xoops_getHandler('groupperm');
        $categories    = $gpermHandler->getItemIds($permtype, $groups, $tdmModule->getVar('mid'));

        return $categories;
    }

    /**
     * @param        $mytree
     * @param        $key
     * @param        $category_array
     * @param        $title
     * @param string $prefix
     * @return string
     */
    public static function getPathTree($mytree, $key, $category_array, $title, $prefix = '')
    {
        $category_parent = $mytree->getAllParent($key);
        $category_parent = array_reverse($category_parent);
        $Path            = '';
        foreach (array_keys($category_parent) as $j) {
            $Path .= $category_parent[$j]->getVar($title) . $prefix;
        }
        if (array_key_exists($key, $category_array)) {
            $first_category = $category_array[$key]->getVar($title);
        } else {
            $first_category = '';
        }
        $Path .= $first_category;

        return $Path;
    }

    /**
     * @param        $global
     * @param        $key
     * @param string $default
     * @param string $type
     * @return mixed|string
     */
    public static function cleanVars(&$global, $key, $default = '', $type = 'int')
    {
        switch ($type) {
            case 'string':
                $ret = isset($global[$key]) ? filter_var($global[$key], FILTER_SANITIZE_MAGIC_QUOTES) : $default;
                break;
            case 'int':
            default:
                $ret = isset($global[$key]) ? filter_var($global[$key], FILTER_SANITIZE_NUMBER_INT) : $default;
                break;
        }
        if ($ret === false) {
            return $default;
        }

        return $ret;
    }
    /**
     * @param  string $item
     * @return string
     */
    public static function convertCharset($item)
    {
        if (_CHARSET !== 'windows-1256') {
            return utf8_encode($item);
        }

        if ($unserialize == unserialize($item)) {
            foreach ($unserialize as $key => $value) {
                $unserialize[$key] = @iconv('windows-1256', 'UTF-8', $value);
            }
            $serialize = serialize($unserialize);

            return $serialize;
        } else {
            return @iconv('windows-1256', 'UTF-8', $item);
        }
    }
}
