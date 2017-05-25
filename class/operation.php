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

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**
 * Class TdmMoneyOperation
 */
class TdmMoneyOperation extends XoopsObject
{
    //Constructor
    /**
     * TdmMoneyOperation constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('operation_id', XOBJ_DTYPE_INT, null, false, 8);
        $this->initVar('operation_account', XOBJ_DTYPE_INT, null, false, 8);
        $this->initVar('operation_category', XOBJ_DTYPE_INT, null, false, 8);
        $this->initVar('operation_type', XOBJ_DTYPE_INT, null, false, 8);
        $this->initVar('operation_sender', XOBJ_DTYPE_INT, null, false, 8);
        $this->initVar('operation_outsender', XOBJ_DTYPE_TXTBOX, null, false);
        $this->initVar('operation_date', XOBJ_DTYPE_INT, null, false, 10);
        $this->initVar('operation_amount', XOBJ_DTYPE_OTHER, null, false, 8);
        $this->initVar('operation_description', XOBJ_DTYPE_TXTAREA, null, false);
        $this->initVar('operation_submitter', XOBJ_DTYPE_INT, null, false, 10);
        $this->initVar('operation_date_created', XOBJ_DTYPE_INT, null, false, 10);
        // Pour autoriser le html
        $this->initVar('dohtml', XOBJ_DTYPE_INT, 1, false);

        $this->initVar('cat_title', XOBJ_DTYPE_TXTBOX, null, false);
        $this->initVar('account_name', XOBJ_DTYPE_TXTBOX, null, false);
        $this->initVar('account_balance', XOBJ_DTYPE_INT, null, false, 10);
        $this->initVar('account_currency', XOBJ_DTYPE_TXTBOX, null, false);
    }

    //    public function tdmmoney_operation()
    //    {
    //        $this->__construct();
    //    }

    /**
     * @param bool $action
     * @return XoopsThemeForm
     */
    public function getForm($action = false)
    {
        global $xoopsDB, $xoopsModuleConfig, $xoopsUser;

        if ($action === false) {
            $action = $_SERVER['REQUEST_URI'];
        }
        //nom du formulaire selon l'action (editer ou ajouter):
        $title = $this->isNew() ? sprintf(_AM_TDMMONEY_OPERATION_ADD) : sprintf(_AM_TDMMONEY_OPERATION_EDIT, $this->getVar('operation_id'));
        //création du formulaire
        $form = new XoopsThemeForm($title, 'form', $action, 'post', true);
        $form->setExtra('enctype="multipart/form-data"');
        //type d'opération
        $type    = new XoopsFormRadio(_AM_TDMMONEY_OPERATION_TYPE, 'operation_type', $this->isNew() ? 1 : $this->getVar('operation_type', 'e'));
        $options = array(
            1 => _AM_TDMMONEY_OPERATION_WITHDRAW,
            2 => _AM_TDMMONEY_OPERATION_DEPOSIT
        );
        $type->addOptionArray($options);
        $form->addElement($type, true);
        //choix du compte
        // pour les permissions
        $accountHandler = xoops_getModuleHandler('account', 'TDMMoney');
        $access_account = TdmmoneyUtility::getMygetItemIds('tdmmoney_submit', 'TDMMoney');
        $criteria       = new CriteriaCompo();
        $criteria->setSort('account_name');
        $criteria->setOrder('ASC');
        if ($xoopsUser) {
            $xoopsModule = XoopsModule::getByDirname('TDMMoney');
            if (!$xoopsUser->isAdmin($xoopsModule->mid())) {
                $criteria->add(new Criteria('account_id', '(' . implode(',', $access_account) . ')', 'IN'));
            }
        } else {
            $criteria->add(new Criteria('account_id', '(' . implode(',', $access_account) . ')', 'IN'));
        }
        if ($accountHandler->getCount($criteria) == 0) {
            redirect_header('index.php', 2, _NOPERM);
        }
        $account_select = new XoopsFormSelect(_AM_TDMMONEY_ACCOUNT_NAME, 'operation_account', $this->getVar('operation_account'));
        $account_select->addOptionArray($accountHandler->getList($criteria));
        $form->addElement($account_select, true);
        //tiers
        include_once XOOPS_ROOT_PATH . '/modules/tdmmoney/class/formselectuser.php';
        $sender = new XoopsFormElementTray(_AM_TDMMONEY_OPERATION_SENDER);
        $sender->addElement(new TDMXoopsFormSelectUser('', 'operation_sender', false, _AM_TDMMONEY_OPERATION_OUTSIDE, $this->getVar('operation_sender'), 1, false), true);
        $outsender = new XoopsFormText(_AM_TDMMONEY_OPERATION_OUTSENDER, 'operation_outsender', 25, 50, $this->getVar('operation_outsender'));
        $sender->addElement($outsender);
        $form->addElement($sender);
        //choix de la catégorie
        $categoryHandler = xoops_getModuleHandler('category', 'tdmmoney');
        $criteria        = new CriteriaCompo();
        $criteria->setSort('cat_weight ASC, cat_title');
        $criteria->setOrder('ASC');
        $category_arr = $categoryHandler->getall($criteria);
        $mytree       = new XoopsObjectTree($category_arr, 'cat_cid', 'cat_pid');
//        $form->addElement(new XoopsFormLabel(_AM_TDMMONEY_OPERATION_CATEGORY, $mytree->makeSelBox('operation_category', 'cat_title', '--', $this->getVar('operation_category'), false)), true);


        if (TdmMoneyUtility::checkXoopsVersion('2', '5', '9', '>=')) {
            $catSelect = $mytree->makeSelectElement('operation_category', 'cat_title', '--', $this->getVar('operation_category'), false, 0, '', _AM_TDMMONEY_OPERATION_CATEGORY);
            $form->addElement($catSelect);
        } else {
            $form->addElement(new XoopsFormLabel(_AM_TDMMONEY_OPERATION_CATEGORY, $mytree->makeSelBox('operation_category', 'cat_title', '--', $this->getVar('operation_category'), false)), true);
        }





        //date
        $form->addElement(new XoopsFormTextDateSelect(_AM_TDMMONEY_OPERATION_DATE, 'operation_date', '', $this->getVar('operation_date')), true);
        //montant
        $form->addElement(new XoopsFormText(_AM_TDMMONEY_OPERATION_AMOUNT, 'operation_amount', 10, 10, $this->getVar('operation_amount')), true);
        //description
        $editor_configs           = array();
        $editor_configs['name']   = 'operation_description';
        $editor_configs['value']  = $this->getVar('operation_description', 'e');
        $editor_configs['rows']   = 5;
        $editor_configs['cols']   = 80;
        $editor_configs['width']  = '100%';
        $editor_configs['height'] = '400px';
        $editor_configs['editor'] = $xoopsModuleConfig['TdmMoneyEditor'];
        $form->addElement(new XoopsFormEditor(_AM_TDMMONEY_OPERATION_DESCRIPTION, 'operation_description', $editor_configs));
        //pour enregistrer le formulaire
        $form->addElement(new XoopsFormHidden('op', 'save'));
        // pour passer "operation_id" si on modifie le compte
        if (!$this->isNew()) {
            $form->addElement(new XoopsFormHidden('operation_id', $this->getVar('operation_id')));
        }
        //boutton d'envoi du formulaire
        $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

        return $form;
    }
}

/**
 * Class TdmMoneyOperationHandler
 */
class TdmMoneyOperationHandler extends XoopsPersistableObjectHandler
{
    /**
     * TdmMoneyOperationHandler constructor.
     * @param null|object|XoopsDatabase $db
     */
    public function __construct($db)
    {
        parent::__construct($db, 'tdmmoney_operation', 'TdmMoneyOperation', 'operation_id', 'operation_amount');
    }
}
