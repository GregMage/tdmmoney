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

// Nom du module
define('_MI_TDMMONEY_NAME', 'TDMMoney');

// Description du module
define('_MI_TDMMONEY_DESC', 'TDMMoney enables to organize and manage personal finances, or other association.');

//Menu
define('_MI_TDMMONEY_MANAGER_INDEX', 'Index');
define('_MI_TDMMONEY_MANAGER_ACCOUNT', 'Accounts');
define('_MI_TDMMONEY_MANAGER_CATEGORY', 'Categories');
define('_MI_TDMMONEY_MANAGER_OPERATION', 'Operations');
define('_MI_TDMMONEY_MANAGER_ABOUT', 'About');
define('_MI_TDMMONEY_MANAGER_PERMISSIONS', 'Permissions');

//Config
define('_MI_TDMMONEY_EDITOR', 'Text Editor');
define('_MI_TDMMONEY_FILTER', 'Filtering operations by default');
define('_MI_TDMMONEY_FILTER1', 'No filter');
define('_MI_TDMMONEY_FILTER2', 'Current Month');
define('_MI_TDMMONEY_FILTER3', 'Current Year');

//Sous menu
define('_MI_TDMMONEY_SUBMIT', 'Add an operation');
//1.2
//Help
define('_MI_TDMMONEY_DIRNAME', basename(dirname(dirname(__DIR__))));
define('_MI_TDMMONEY_HELP_HEADER', __DIR__ . '/help/helpheader.html');
define('_MI_TDMMONEY_BACK_2_ADMIN', 'Back to Administration of ');
define('_MI_TDMMONEY_OVERVIEW', 'Overview');

//define('_MI_TDMMONEY_HELP_DIR', __DIR__);

//help multi-page
define('_MI_TDMMONEY_DISCLAIMER', 'Disclaimer');
define('_MI_TDMMONEY_LICENSE', 'License');
define('_MI_TDMMONEY_SUPPORT', 'Support');
define('_MI_TDMMONEY_SHOW_SAMPLE_DATA', 'Show "Load Sample Data" Button');
define('_MI_TDMMONEY_DISPLAY_PDF', 'Display PDF Icon');
define('_MI_TDMMONEY_DISPLAY_PDF_DSC',
       'Select Yes to show PDF icon and allow users to create PDF files <br>Make sure you have the TCPDF library installed. Please read the "readme.txt" file in /docs folder for info how to get it.');
