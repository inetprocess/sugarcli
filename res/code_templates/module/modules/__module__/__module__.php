<?php
    /*
     * Your installation or use of this SugarCRM file is subject to the applicable
     * terms available at
     * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
     * If you do not agree to all of the applicable terms or do not have the
     * authority to bind the entity as an authorized representative, then do not
     * install or use this SugarCRM file.
     *
     * Copyright (C) SugarCRM Inc. All rights reserved.
     */

    require_once('modules/__module__/__module___sugar.php');

    class __module__ extends __module___sugar {

        /**
         * This is a depreciated method, please start using __construct() as this method will be removed in a future version
         *
         * @see __construct
         * @depreciated
         */
        function __module__() {
            self::__construct();
        }

        public function __construct() {
            parent::__construct();
        }
    }