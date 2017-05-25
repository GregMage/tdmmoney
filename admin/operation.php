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
//$pathIcon16      = \Xmf\Module\Admin::iconUrl('', 16);

$account_id = TdmmoneyUtility::cleanVars($_REQUEST, 'account_id', 0, 'int');

// Sous-menu
$menu_account = '<form id="form_account" name="form_account" method="get" action="operation.php">';
$menu_account .= _AM_TDMMONEY_OPERATION_LISTBYACCOUNT;
$menu_account .= "<select name=\"account_tri\" id=\"account_tri\" onchange=\"location='" . XOOPS_URL . '/modules/' . $xoopsModule->dirname()
                 . "/admin/operation.php?op=list&account_id='+this.options[this.selectedIndex].value\">";
$criteria     = new CriteriaCompo();
$criteria->setSort('account_name');
$criteria->setOrder('ASC');
$account_arr  = $accountHandler->getall($criteria);
$menu_account .= '<option value="0"' . ($account_id == 0 ? ' selected="selected"' : '') . '>' . _AM_TDMMONEY_OPERATION_ALL . '</option>';
foreach (array_keys($account_arr) as $i) {
    $menu_account .= '<option value="' . $i . '"' . ($account_id == $i ? ' selected="selected"' : '') . '>' . $account_arr[$i]->getVar('account_name') . '</option>';
}
$menu_account .= '</select></form>';

//Les valeurs de op qui vont permettre d'aller dans les differentes parties de la page
switch ($op) {
    // Vue liste
    case 'list':
        //Affichage de la partie haute de l'administration de Xoops
        xoops_cp_header();

        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->addItemButton(_AM_TDMMONEY_OPERATION_NEW, 'operation.php?op=new', 'add');
        echo $menu_account;
        $adminObject->displayButton('left');

        $criteria = new CriteriaCompo();
        // gestion de l'affichage des comptes
        if ($account_id == 0) {
            $display_account = false;
        } else {
            // affichage des filtres
            $display_account = true;
            $criteria->add(new Criteria('operation_account', $account_id));
            if (isset($_REQUEST['date_start']) && isset($_REQUEST['date_end'])) {
                $date_start = strtotime($_REQUEST['date_start']);
                $date_end   = strtotime($_REQUEST['date_end']);
            } else {
                //sans filtre
                if ($xoopsModuleConfig['TdmMoneyFilter'] == 1) {
                    $date_start = 0;
                    $date_end   = mktime(0, 0, 0, 12, 31, date('Y'));
                }
                //filtre mois actuel
                if ($xoopsModuleConfig['TdmMoneyFilter'] == 2) {
                    $date_start = mktime(0, 0, 0, date('m'), 1, date('Y'));
                    $date_end   = mktime(0, 0, 0, date('m'), date('t'), date('Y'));
                }
                //filtre année actuelle
                if ($xoopsModuleConfig['TdmMoneyFilter'] == 3) {
                    $date_start = mktime(0, 0, 0, 1, 1, date('Y'));
                    $date_end   = mktime(0, 0, 0, 12, 31, date('Y'));
                }
            }
            $criteria->add(new Criteria('operation_date', $date_start, '>='));
            $criteria->add(new Criteria('operation_date', $date_end, '<='));
            $form = new XoopsThemeForm(_AM_TDMMONEY_OPERATION_FILTER, 'form', 'operation.php', 'post', true);
            $form->setExtra('enctype="multipart/form-data"');
            $filer = new XoopsFormElementTray(_AM_TDMMONEY_OPERATION_FILTER);
            $filer->addElement(new XoopsFormTextDateSelect(_AM_TDMMONEY_OPERATION_START, 'date_start', '', $date_start));
            $filer->addElement(new XoopsFormTextDateSelect(_AM_TDMMONEY_OPERATION_END, 'date_end', '', $date_end));
            $filer->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
            $form->addElement($filer);
            $form->addElement(new XoopsFormHidden('op', 'list'));
            $form->addElement(new XoopsFormHidden('account_id', $account_id));
            $form->display();
        }
        // extraction des données
        $criteria->setSort('operation_date');
        $criteria->setOrder('DESC');
        //pour faire une jointure de table
        $operationHandler->table_link   = $operationHandler->db->prefix('tdmmoney_account'); // Nom de la table en jointure
        $operationHandler->field_link   = 'account_id'; // champ de la table en jointure
        $operationHandler->field_object = 'operation_account'; // champ de la table courante

        $operation_arr = $operationHandler->getByLink($criteria);
        if (count($operation_arr) > 0) {
            // si en vue compte on calcul les soldes
            if ($display_account === true) {
                $operation_balance_arr = array_reverse($operation_arr, true);
                $account_balance       = $accountHandler->get($account_id);
                $balance               = $account_balance->getVar('account_balance');
                $criteria_amount       = new CriteriaCompo();
                $criteria_amount->add(new Criteria('operation_account', $account_id));
                $criteria_amount->add(new Criteria('operation_date', $date_start, '<'));
                $operation_ammount = $operationHandler->getall($criteria_amount);
                $balance_ammount   = 0;
                foreach (array_keys($operation_ammount) as $i) {
                    $balance_ammount = $operation_ammount[$i]->getVar('operation_type') == 1 ? $balance_ammount - $operation_ammount[$i]->getVar('operation_amount') : $balance_ammount
                                                                                                                                                                       + $operation_ammount[$i]->getVar('operation_amount');
                }
                $balance      += $balance_ammount;
                $balance_save = $balance;
                foreach (array_keys($operation_balance_arr) as $i) {
                    $balance               = $operation_balance_arr[$i]->getVar('operation_type') == 1 ? $balance - $operation_balance_arr[$i]->getVar('operation_amount') : $balance
                                                                                                                                                                             + $operation_balance_arr[$i]->getVar('operation_amount');
                    $operation_balance[$i] = $balance;
                }
            }
            // affichage du header
            echo '<table width="100%" cellspacing="1" class="outer">';
            echo '<tr>';
            if ($display_account === false) {
                echo '<th align="left" width="10%">' . _AM_TDMMONEY_OPERATION_ACCOUNT . '</th>';
            }
            echo '<th align="left" width="10%">' . _AM_TDMMONEY_OPERATION_DATE . '</th>';
            echo '<th align="left" width="10%">' . _AM_TDMMONEY_OPERATION_SENDER . '</th>';
            echo '<th align="left" width="20%">' . _AM_TDMMONEY_OPERATION_CATEGORY . '</th>';
            echo '<th align="left">' . _AM_TDMMONEY_OPERATION_DESCRIPTION . '</th>';
            echo '<th align="center" width="8%">' . _AM_TDMMONEY_OPERATION_WITHDRAW . '</th>';
            echo '<th align="center" width="8%">' . _AM_TDMMONEY_OPERATION_DEPOSIT . '</th>';
            if ($display_account === true) {
                echo '<th align="right"  width="8%">' . _AM_TDMMONEY_OPERATION_BALANCE . '</th>';
            }
            echo '<th align="center" width="8%">' . _AM_TDMMONEY_ACTION . '</th>';
            echo '</tr>';
            $class = 'odd';
            // début de l'affichage des opérations
            $category_arr = $categoryHandler->getall();
            include_once XOOPS_ROOT_PATH . '/modules/tdmmoney/class/tree.php';
            $mytree       = new TdmObjectTree($category_arr, 'cat_cid', 'cat_pid');
            foreach (array_keys($operation_arr) as $i) {
                $category = TdmmoneyUtility::getPathTree($mytree, $operation_arr[$i]->getVar('operation_category'), $category_arr, 'cat_title', $prefix = ' <img src="../assets/images/deco/arrow.gif"> ');
                if ($operation_arr[$i]->getVar('operation_sender') == 0) {
                    if ($operation_arr[$i]->getVar('operation_outsender') == '') {
                        $sender = XoopsUser::getUnameFromId($operation_arr[$i]->getVar('operation_sender'), 1);
                    } else {
                        $sender = $operation_arr[$i]->getVar('operation_outsender');
                    }
                } else {
                    $sender = XoopsUser::getUnameFromId($operation_arr[$i]->getVar('operation_sender'), 1);
                }
                echo '<tr class="' . $class . '">';
                if ($display_account === false) {
                    echo '<td align="left" >' . $operation_arr[$i]->getVar('account_name') . '</td>';
                }
                echo '<td align="left" >' . formatTimestamp($operation_arr[$i]->getVar('operation_date'), 's') . '</td>';
                echo '<td align="left">' . $sender . '</td>';
                echo '<td align="left">' . $category . '</td>';
                echo '<td align="left">' . $operation_arr[$i]->getVar('operation_description') . '</td>';
                echo '<td align="center">';
                echo $operation_arr[$i]->getVar('operation_type') == 1 ? $operation_arr[$i]->getVar('operation_amount') : '';
                echo '</td>';
                echo '<td align="center">';
                echo $operation_arr[$i]->getVar('operation_type') == 2 ? $operation_arr[$i]->getVar('operation_amount') : '';
                echo '</td>';
                if ($display_account === true) {
                    $display_operation_balance = $operation_balance[$i] < 0 ? '<span style="color: #ff0000; font-weight: bold">' . $operation_balance[$i]
                                                                              . '</span>' : '<span style="font-weight: bold">' . $operation_balance[$i] . '</span>';
                    echo '<td align="right">' . $display_operation_balance . ' ' . $operation_arr[$i]->getVar('account_currency') . '</td>';
                }
                echo '<td align="center">';
                echo '<a href="operation.php?op=edit&operation_id=' . $i . '"><img src="' . $pathIcon16 . '/edit.png" alt="' . _AM_TDMMONEY_EDIT . '" title="' . _AM_TDMMONEY_EDIT . '"></a> ';
                echo '<a href="operation.php?op=del&operation_id=' . $i . '&account_id=' . $operation_arr[$i]->getVar('operation_account') . '"><img src="' . $pathIcon16 . '/delete.png" alt="'
                     . _AM_TDMMONEY_DEL . '" title="' . _AM_TDMMONEY_DEL . '"></a>';
                echo '</td>';
                echo '</tr>';
                $class = ($class === 'even') ? 'odd' : 'even';
            }
            if ($display_account === true) {
                echo '<tr class="' . $class . '">';
                $display_account_balance = $balance_save < 0 ? '<span style="color: #ff0000; font-weight: bold">' . $balance_save . '</span>' : '<span style="font-weight: bold">' . $balance_save
                                                                                                                                                . '</span>';
                echo '<td align="right" colspan="6">' . _AM_TDMMONEY_OPERATION_REPORT . formatTimestamp($date_start - 86400, 's') . '</td>';
                echo '<td align="right">' . $display_account_balance . ' ' . $operation_arr[$i]->getVar('account_currency') . '</td>';
                echo '<td align="center"></td>';
                echo '</tr>';
            }
            echo '</table>';
            // export en pdf
            if ($display_account === true) {
                echo '<br><div align="center">' . _AM_TDMMONEY_OPERATION_EXPORTPDF . ': <a href="../makepdf.php?account_id=' . $account_id . '&date_start=' . $date_start . '&date_end='
                     . $date_end . '"><img src="../assets/images/deco/pdf.png"></a></div>';
            }
        }
        break;

    // vue création
    case 'new':
        //Affichage de la partie haute de l'administration de Xoops
        xoops_cp_header();

        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->addItemButton(_AM_TDMMONEY_OPERATION_LIST, 'operation.php?op=list', 'list');
        $adminObject->displayButton('left');

        //Affichage du formulaire de création des opérations
        $obj  = $operationHandler->create();
        $form = $obj->getForm();
        $form->display();
        break;

    // Pour éditer une opération
    case 'edit':
        //Affichage de la partie haute de l'administration de Xoops
        xoops_cp_header();

        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->addItemButton(_AM_TDMMONEY_OPERATION_NEW, 'operation.php?op=new', 'add');
        $adminObject->addItemButton(_AM_TDMMONEY_OPERATION_LIST, 'operation.php?op=list', 'list');
        $adminObject->displayButton('left');

        //Affichage du formulaire de création des opérations
        $operation_id = TdmmoneyUtility::cleanVars($_REQUEST, 'operation_id', 0, 'int');
        $obj          = $operationHandler->get($operation_id);
        $form         = $obj->getForm();
        $form->display();
        break;

    // Pour supprimer une opération
    case 'del':
        //Affichage de la partie haute de l'administration de Xoops
        xoops_cp_header();

        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->addItemButton(_AM_TDMMONEY_OPERATION_NEW, 'operation.php?op=new', 'add');
        $adminObject->addItemButton(_AM_TDMMONEY_OPERATION_LIST, 'operation.php?op=list', 'list');
        $adminObject->displayButton('left');

        global $xoopsModule;
        $operation_id = TdmmoneyUtility::cleanVars($_REQUEST, 'operation_id', 0, 'int');
        $account_id   = TdmmoneyUtility::cleanVars($_REQUEST, 'account_id', 0, 'int');
        $obj          = $operationHandler->get($operation_id);
        if (isset($_REQUEST['ok']) && $_REQUEST['ok'] == 1) {
            if (!$GLOBALS['xoopsSecurity']->check()) {
                redirect_header('operation.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
            }
            // supression de l'opération
            if ($operationHandler->delete($obj)) {
                redirect_header('operation.php?op=list&account_id=' . $account_id, 1, _AM_TDMMONEY_OPERATION_DELOK);
            } else {
                echo $obj->getHtmlErrors();
            }
        } else {
            xoops_confirm(array('ok'           => 1,
                                'operation_id' => $operation_id,
                                'op'           => 'del',
                                'account_id'   => $account_id
                          ), $_SERVER['REQUEST_URI'], sprintf(_AM_TDMMONEY_SUREDEL, $obj->getVar('operation_id')) . ' (' . $obj->getVar('operation_amount') . ')');
        }
        break;

    // Pour sauver une opération
    case 'save':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header('operation.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        $operation_id = TdmmoneyUtility::cleanVars($_REQUEST, 'operation_id', 0, 'int');
        if (isset($_REQUEST['operation_id'])) {
            $obj = $operationHandler->get($operation_id);
        } else {
            $obj = $operationHandler->create();
        }
        $erreur         = false;
        $message_erreur = '';
        //Récupération des variables:
        $obj->setVar('operation_account', $_POST['operation_account']);
        $obj->setVar('operation_category', $_POST['operation_category']);
        $obj->setVar('operation_type', $_POST['operation_type']);
        $obj->setVar('operation_date', strtotime($_POST['operation_date']));
        $obj->setVar('operation_amount', $_POST['operation_amount']);
        $obj->setVar('operation_description', $_POST['operation_description']);
        $obj->setVar('operation_submitter', !empty($xoopsUser) ? $xoopsUser->getVar('uid') : 0);
        $obj->setVar('operation_date_created', time());
        if ($_POST['operation_sender'] == 0) {
            $obj->setVar('operation_sender', 0);
            $obj->setVar('operation_outsender', $_POST['operation_outsender']);
        } else {
            $obj->setVar('operation_sender', $_POST['operation_sender']);
            $obj->setVar('operation_outsender', '');
        }
        //vérification que operation_amount soit un entier
        if ((int)$_REQUEST['operation_amount'] == 0 && $_REQUEST['operation_amount'] != '0') {
            $erreur         = true;
            $message_erreur = _AM_TDMMONEY_OPERATION_ERREUR_AMOUNT . '<br>';
        }
        if ($erreur === true) {
            echo '<div class="errorMsg" style="text-align: left;">' . $message_erreur . '</div>';
        } else {
            if ($operationHandler->insert($obj)) {
                redirect_header('operation.php?op=list&account_id=' . $_POST['operation_account'], 1, _AM_TDMMONEY_OPERATION_SAVE);
            }
            echo $obj->getHtmlErrors();
        }
        $form =& $obj->getForm();
        $form->display();
        break;
}
include_once __DIR__ . '/admin_footer.php';
