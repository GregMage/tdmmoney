<?php
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    XOOPS Project http://xoops.org/
 * @license      GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package
 * @since
 * @author       XOOPS Development Team
 */

//require_once __DIR__ . '/setup.php';

/**
 *
 * Prepares system prior to attempting to install module
 * @param XoopsModule $module {@link XoopsModule}
 *
 * @return bool true if ready to install, false if not
 */
function xoops_module_pre_install_tdmmoney(XoopsModule $module)
{
    $moduleDirName = basename(dirname(__DIR__));
    $classUtil     = ucfirst($moduleDirName) . 'Utility';
    if (!class_exists($classUtil)) {
        xoops_load('utility', $moduleDirName);
    }
    //check for minimum XOOPS version
    if (!$classUtil::checkVerXoops($module)) {
        return false;
    }

    // check for minimum PHP version
    if (!$classUtil::checkVerPhp($module)) {
        return false;
    }

    $mod_tables =& $module->getInfo('tables');
    foreach ($mod_tables as $table) {
        $GLOBALS['xoopsDB']->queryF('DROP TABLE IF EXISTS ' . $GLOBALS['xoopsDB']->prefix($table) . ';');
    }

    return true;
}

/**
 *
 * Performs tasks required during installation of the module
 * @param XoopsModule $module {@link XoopsModule}
 *
 * @return bool true if installation successful, false if not
 */
function xoops_module_install_tdmmoney(XoopsModule $module)
{
    include_once dirname(dirname(dirname(__DIR__))) . '/mainfile.php';
    include_once dirname(__DIR__) . '/include/config.php';

    if (!isset($moduleDirName)) {
        $moduleDirName = basename(dirname(__DIR__));
    }

    if (false !== ($moduleHelper = Xmf\Module\Helper::getHelper($moduleDirName))) {
    } else {
        $moduleHelper = Xmf\Module\Helper::getHelper('system');
    }

    // Load language files
    $moduleHelper->loadLanguage('admin');
    $moduleHelper->loadLanguage('modinfo');

    $configurator = new ModuleConfigurator();
    $classUtil    = ucfirst($moduleDirName) . 'Utility';
    if (!class_exists($classUtil)) {
        xoops_load('utility', $moduleDirName);
    }

    // default Permission Settings ----------------------
    global $xoopsModule;
    $moduleId     = $xoopsModule->getVar('mid');
    $moduleId2    = $moduleHelper->getModule()->mid();
    /* @var $gpermHandler XoopsGroupPermHandler  */
    $gpermHandler = xoops_getHandler('groupperm');
    // access rights ------------------------------------------
    $gpermHandler->addRight($moduleDirName . '_approve', 1, XOOPS_GROUP_ADMIN, $moduleId);
    $gpermHandler->addRight($moduleDirName . '_submit', 1, XOOPS_GROUP_ADMIN, $moduleId);
    $gpermHandler->addRight($moduleDirName . '_view', 1, XOOPS_GROUP_ADMIN, $moduleId);
    $gpermHandler->addRight($moduleDirName . '_view', 1, XOOPS_GROUP_USERS, $moduleId);
    $gpermHandler->addRight($moduleDirName . '_view', 1, XOOPS_GROUP_ANONYMOUS, $moduleId);

    //  ---  CREATE FOLDERS ---------------
    if (count($configurator->uploadFolders) > 0) {
        //    foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
        foreach (array_keys($configurator->uploadFolders) as $i) {
            $classUtil::createFolder($configurator->uploadFolders[$i]);
        }
    }

    //  ---  COPY blank.png FILES ---------------
    if (count($configurator->blankFiles) > 0) {
        $file = __DIR__ . '/../assets/images/blank.png';
        foreach (array_keys($configurator->blankFiles) as $i) {
            $dest = $configurator->blankFiles[$i] . '/blank.png';
            $classUtil::copyFile($file, $dest);
        }
    }

    return true;
}
