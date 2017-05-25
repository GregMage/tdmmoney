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

//On recupere la valeur de l'argument op dans l'URL$
$op = TdmmoneyUtility::cleanVars($_REQUEST, 'op', 'list', 'string');

//Les valeurs de op qui vont permettre d'aller dans les differentes parties de la page
switch ($op) {
    // Vue liste
    case 'list':
        //Affichage de la partie haute de l'administration de Xoops
        xoops_cp_header();

        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->addItemButton(_AM_TDMMONEY_ACCOUNT_NEW, 'account.php?op=new', 'add');

        if ($moduleHelper->getConfig('ShowSampleDataButton')) {
            require_once __DIR__ . '/../testdata/index.php';
            $adminObject->addItemButton(_AM_TDMMONEY_ADD_SAMPLEDATA, '__DIR__ . /../../testdata/index.php?op=load', 'add');
        }
        $adminObject->displayButton('left');

        $criteria = new CriteriaCompo();
        $criteria->setSort('account_name');
        $criteria->setOrder('ASC');
        $account_arr = $accountHandler->getAll($criteria);
        if (count($account_arr) > 0) {
            //pour le calcul des soldes:
            $criteria = new CriteriaCompo();
            $criteria->setSort('operation_account');
            $criteria->setOrder('ASC');
            $operation_arr = $operationHandler->getall($criteria);

            echo '<table width="100%" cellspacing="1" class="outer">';
            echo '<tr>';
            echo '<th align="center">' . _AM_TDMMONEY_ACCOUNT_NAME . '</th>';
            echo '<th align="center">' . _AM_TDMMONEY_ACCOUNT_BANK . '</th>';
            echo '<th align="center">' . _AM_TDMMONEY_ACCOUNT_ADRESS . '</th>';
            echo '<th align="center">' . _AM_TDMMONEY_ACCOUNT_BALANCE . '</th>';
            echo '<th align="center">' . _AM_TDMMONEY_ACCOUNT_BALANCE2 . '</th>';
            echo '<th align="center" width="10%">' . _AM_TDMMONEY_ACTION . '</th>';
            echo '</tr>';
            $class = 'odd';
            foreach (array_keys($account_arr) as $i) {
                //calcul des soldes
                $balance_operation = $account_arr[$i]->getVar('account_balance');
                foreach (array_keys($operation_arr) as $j) {
                    if ($operation_arr[$j]->getVar('operation_account') == $account_arr[$i]->getVar('account_id')) {
                        $balance_operation = $operation_arr[$j]->getVar('operation_type') == 1 ? $balance_operation - $operation_arr[$j]->getVar('operation_amount') : $balance_operation
                                                                                                                                                                       + $operation_arr[$j]->getVar('operation_amount');
                    }
                }
                echo '<tr class="' . $class . '">';
                echo '<td align="center" >' . $account_arr[$i]->getVar('account_name') . '</td>';
                echo '<td align="center">' . $account_arr[$i]->getVar('account_bank') . '</td>';
                echo '<td align="center">' . $account_arr[$i]->getVar('account_adress') . '</td>';
                $display_account_balance = $account_arr[$i]->getVar('account_balance') < 0 ? '<span style="color: #ff0000; font-weight: bold;">' . $account_arr[$i]->getVar('account_balance')
                                                                                             . '</span>' : '<span style="font-weight: bold;">' . $account_arr[$i]->getVar('account_balance') . '</span>';
                echo '<td align="center">' . $display_account_balance . ' ' . $account_arr[$i]->getVar('account_currency') . '</td>';
                $display_balance_operation = $balance_operation < 0 ? '<span style="color: #ff0000; font-weight: bold;">' . $balance_operation . '</span>' : '<span style="font-weight: bold">'
                                                                                                                                                            . $balance_operation . '</span>';
                echo '<td align="center">' . $display_balance_operation . ' ' . $account_arr[$i]->getVar('account_currency') . '</td>';
                echo '<td align="center" width="10%">';
                echo '<a href="operation.php?op=list&account_id=' . $i . '"><img src="' . $pathIcon16 . '/view.png" alt="' . _AM_TDMMONEY_DISPLAY . '" title="' . _AM_TDMMONEY_DISPLAY . '"></a> ';
                echo '<a href="account.php?op=edit&account_id=' . $i . '"><img src="' . $pathIcon16 . '/edit.png" alt="' . _AM_TDMMONEY_EDIT . '" title="' . _AM_TDMMONEY_EDIT . '"></a> ';
                echo '<a href="account.php?op=del&account_id=' . $i . '"><img src="' . $pathIcon16 . '/delete.png" alt="' . _AM_TDMMONEY_DEL . '" title="' . _AM_TDMMONEY_DEL . '"></a>';
                echo '</td>';
                echo '</tr>';
                $class = ($class === 'even') ? 'odd' : 'even';
            }
            echo '</table>';
        }
        break;

    // vue création
    case 'new':
        //Affichage de la partie haute de l'administration de Xoops
        xoops_cp_header();

        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->addItemButton(_AM_TDMMONEY_ACCOUNT_LIST, 'account.php?op=list', 'list');
        $adminObject->displayButton('left');

        //Affichage du formulaire de création des comptes
        $obj  = $accountHandler->create();
        $form = $obj->getForm();
        $form->display();
        break;

    // Pour éditer un compte
    case 'edit':
        //Affichage de la partie haute de l'administration de Xoops
        xoops_cp_header();

        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->addItemButton(_AM_TDMMONEY_ACCOUNT_NEW, 'account.php?op=new', 'add');
        $adminObject->addItemButton(_AM_TDMMONEY_ACCOUNT_LIST, 'account.php?op=list', 'list');
        $adminObject->displayButton('left');

        //Affichage du formulaire de création des comptes
        $account_id = TdmmoneyUtility::cleanVars($_REQUEST, 'account_id', 0, 'int');
        $obj        = $accountHandler->get($account_id);
        $form       = $obj->getForm();
        $form->display();
        break;

    // Pour supprimer un compte
    case 'del':
        //Affichage de la partie haute de l'administration de Xoops
        xoops_cp_header();

        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->addItemButton(_AM_TDMMONEY_ACCOUNT_NEW, 'account.php?op=new', 'add');
        $adminObject->addItemButton(_AM_TDMMONEY_ACCOUNT_LIST, 'account.php?op=list', 'list');
        $adminObject->displayButton('left');

        global $xoopsModule;
        $account_id = TdmmoneyUtility::cleanVars($_REQUEST, 'account_id', 0, 'int');
        $obj        = $accountHandler->get($account_id);
        if (isset($_REQUEST['ok']) && $_REQUEST['ok'] == 1) {
            if (!$GLOBALS['xoopsSecurity']->check()) {
                redirect_header('account.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
            }
            // supression des opérations du compte
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('operation_account', $account_id));
            $contents_arr = $operationHandler->getall($criteria);
            foreach (array_keys($contents_arr) as $i) {
                $objcontents = $operationHandler->get($contents_arr[$i]->getVar('operation_id'));
                $operationHandler->delete($objcontents) or $objcontents->getHtmlErrors();
            }
            if ($accountHandler->delete($obj)) {
                redirect_header('account.php', 1, _AM_TDMMONEY_ACCOUNT_DELOK);
            } else {
                echo $obj->getHtmlErrors();
            }
        } else {
            $message  = '';
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('operation_account', $account_id));
            $operation_arr = $operationHandler->getall($criteria);
            if (count($operation_arr) > 0) {
                $message .= _AM_TDMMONEY_CAT_DELOPERATION . '<br>';
                foreach (array_keys($operation_arr) as $i) {
                    $message .= '<span style="color : Red;">' . $operation_arr[$i]->getVar('operation_id') . ' - ' . formatTimestamp($operation_arr[$i]->getVar('operation_date'), 's') . ' ('
                                . $operation_arr[$i]->getVar('operation_amount') . ')</span><br>';
                }
            }
            xoops_confirm(array('ok'         => 1,
                                'account_id' => $account_id,
                                'op'         => 'del'
                          ), $_SERVER['REQUEST_URI'], sprintf(_AM_TDMMONEY_SUREDEL, $obj->getVar('account_name')) . '<br><br>' . $message);
        }
        break;

    // Pour sauver un compte
    case 'save':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header('category.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        $account_id = TdmmoneyUtility::cleanVars($_REQUEST, 'account_id', 0, 'int');
        if (isset($_REQUEST['account_id'])) {
            $obj = $accountHandler->get($account_id);
        } else {
            $obj = $accountHandler->create();
        }
        $erreur         = false;
        $message_erreur = '';
        // Récupération des variables:
        $obj->setVar('account_name', $_POST['account_name']);
        $obj->setVar('account_bank', $_POST['account_bank']);
        $obj->setVar('account_adress', $_POST['account_adress']);
        $obj->setVar('account_balance', $_POST['account_balance']);
        $obj->setVar('account_currency', $_POST['account_currency']);
        //vérification que account_balance soit un entier
        if ((int)$_REQUEST['account_balance'] == 0 && $_REQUEST['account_balance'] != '0') {
            $erreur         = true;
            $message_erreur = _AM_TDMMONEY_ACCOUNT_ERREUR_BALANCE . '<br>';
        }
        if ($erreur === true) {
            echo '<div class="errorMsg" style="text-align: left;">' . $message_erreur . '</div>';
        } else {
            if ($accountHandler->insert($obj)) {
                $newaccount_id = $obj->get_new_enreg();
                //permission pour voir
                $perm_id      = isset($_REQUEST['account_id']) ? $account_id : $newaccount_id;
                /* @var $gpermHandler XoopsGroupPermHandler  */
                $gpermHandler = xoops_getHandler('groupperm');
                $criteria     = new CriteriaCompo();
                $criteria->add(new Criteria('gperm_itemid', $perm_id, '='));
                $criteria->add(new Criteria('gperm_modid', $xoopsModule->getVar('mid'), '='));
                $criteria->add(new Criteria('gperm_name', 'tdmmoney_view', '='));
                $gpermHandler->deleteAll($criteria);
                if (isset($_POST['groups_view'])) {
                    foreach ($_POST['groups_view'] as $onegroup_id) {
                        $gpermHandler->addRight('tdmmoney_view', $perm_id, $onegroup_id, $xoopsModule->getVar('mid'));
                    }
                }
                //permission pour editer
                $perm_id      = isset($_REQUEST['account_id']) ? $account_id : $newaccount_id;
                $gpermHandler = xoops_getHandler('groupperm');
                $criteria     = new CriteriaCompo();
                $criteria->add(new Criteria('gperm_itemid', $perm_id, '='));
                $criteria->add(new Criteria('gperm_modid', $xoopsModule->getVar('mid'), '='));
                $criteria->add(new Criteria('gperm_name', 'tdmmoney_submit', '='));
                $gpermHandler->deleteAll($criteria);
                if (isset($_POST['groups_submit'])) {
                    foreach ($_POST['groups_submit'] as $onegroup_id) {
                        $gpermHandler->addRight('tdmmoney_submit', $perm_id, $onegroup_id, $xoopsModule->getVar('mid'));
                    }
                }

                redirect_header('account.php?op=list', 1, _AM_TDMMONEY_ACCOUNT_SAVE);
            }
            echo $obj->getHtmlErrors();
        }
        $form =& $obj->getForm();
        $form->display();
        break;
}
include_once __DIR__ . '/admin_footer.php';
