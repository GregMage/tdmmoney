<?php
/**
 * TDMMoney
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright   Gregory Mage (Aka Mage)
 * @license     GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @author      Gregory Mage (Aka Mage)
 */

include_once __DIR__ . '/admin_header.php';
//Affichage de la partie haute de l'administration de Xoops
xoops_cp_header();

//$adminObject = new ModuleAdmin();
$adminObject->displayNavigation(basename(__FILE__));

$permission = TdmmoneyUtility::cleanVars($_POST, 'permission', 1, 'int');

$selected                  = array(
    '',
    '',
    ''
);
$selected[$permission - 1] = ' selected';

echo "<form method='post' name='fselperm' action='permissions.php'><table border='0'><tr><td><select name='permission' onChange='document.fselperm.submit()'><option value='1'" . $selected[0] . '>'
     . _AM_TDMMONEY_PERMISSIONS_OTHER . "</option><option value='2'" . $selected[1] . '>' . _AM_TDMMONEY_PERMISSIONS_VIEW . "</option><option value='3'" . $selected[2] . '>'
     . _AM_TDMMONEY_PERMISSIONS_SUBMIT . "</option></select></td></tr><tr><td><input type='submit' name='go'></tr></table></form>";

$moduleId = $xoopsModule->getVar('mid');

switch ($permission) {
    case 1:
        $formTitle             = _AM_TDMMONEY_PERMISSIONS_OTHER;
        $permissionName        = 'tdmmoney_ac';
        $permissionDescription = _AM_TDMMONEY_PERMISSIONS_OTHER_DSC;
        $global_perms_array    = array(
            '4'  => _AM_TDMMONEY_PERMISSIONS_4,
            '8'  => _AM_TDMMONEY_PERMISSIONS_8,
            '16' => _AM_TDMMONEY_PERMISSIONS_16
        );
        break;
    case 2: // View permission
        $formTitle             = _AM_TDMMONEY_PERMISSIONS_VIEW;
        $permissionName        = 'tdmmoney_view';
        $permissionDescription = _AM_TDMMONEY_PERMISSIONS_VIEW_DSC;
        break;
    case 3: // Submit Permission
        $formTitle             = _AM_TDMMONEY_PERMISSIONS_SUBMIT;
        $permissionName        = 'tdmmoney_submit';
        $permissionDescription = _AM_TDMMONEY_PERMISSIONS_SUBMIT_DSC;
        break;
}

$permissionsForm = new XoopsGroupPermForm($formTitle, $moduleId, $permissionName, $permissionDescription, 'admin/permissions.php');
if ($permission == 1) {
    foreach ($global_perms_array as $perm_id => $permissionName) {
        $permissionsForm->addItem($perm_id, $permissionName);
    }
} else {
    $sql    = 'SELECT account_id, account_name FROM ' . $xoopsDB->prefix('tdmmoney_account') . ' ORDER BY account_name';
    $result = $xoopsDB->query($sql);
    if ($result) {
        while ($row = $xoopsDB->fetchArray($result)) {
            $permissionsForm->addItem($row['account_id'], $row['account_name']);
        }
    }
}
echo $permissionsForm->render();
echo "<br><br><br><br>\n";
unset($permissionsForm);

include_once __DIR__ . '/admin_footer.php';
