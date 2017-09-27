<?php
/**
 * CentOS WebPanel Module.
 *
 * @package blesta
 * @subpackage blesta.components.modules.centoswebpanel
 * @copyright Copyright (c) 2010, Phillips Data, Inc.
 * @license http://www.blesta.com/license/ The Blesta License Agreement
 * @link http://www.blesta.com/ Blesta
 */
class Centoswebpanel extends Module
{
    /**
     * @var string The version of this module
     */
    private static $version = '1.0.0';
    /**
     * @var string The authors of this module
     */
    private static $authors = [['name'=>'Phillips Data, Inc.', 'url'=>'http://www.blesta.com']];

    /**
     * Initializes the module.
     */
    public function __construct()
    {
        // Load components required by this module
        Loader::loadComponents($this, ['Input']);

        // Load the language required by this module
        Language::loadLang('centoswebpanel', null, dirname(__FILE__) . DS . 'language' . DS);
    }

    /**
     * Returns the name of this module.
     *
     * @return string The common name of this module
     */
    public function getName()
    {
        return Language::_('Centoswebpanel.name', true);
    }

    /**
     * Returns the version of this module.
     *
     * @return string The current version of this gateway
     */
    public function getVersion()
    {
        return self::$version;
    }

    /**
     * Returns the name and url of the authors of this module.
     *
     * @return array The name and url of the authors of this module
     */
    public function getAuthors()
    {
        return self::$authors;
    }

    /**
     * Returns all tabs to display to an admin when managing a service whose
     * package uses this module.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @return array An array of tabs in the format of method => title.
     *  Example: array('methodName' => "Title", 'methodName2' => "Title2")
     */
    public function getAdminTabs($package)
    {
        return [
            'tabFirewall' => Language::_('Centoswebpanel.tab_firewall', true)
        ];
    }

    /**
     * Returns all tabs to display to a client when managing a service whose
     * package uses this module.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @return array An array of tabs in the format of method => title.
     *  Example: array('methodName' => "Title", 'methodName2' => "Title2")
     */
    public function getClientTabs($package)
    {
        return [
            'tabClientFirewall' => Language::_('Centoswebpanel.tab_client_firewall', true)
        ];
    }

    /**
     * Returns a noun used to refer to a module row (e.g. "Server").
     *
     * @return string The noun used to refer to a module row
     */
    public function moduleRowName()
    {
        return Language::_('Centoswebpanel.module_row', true);
    }

    /**
     * Returns a noun used to refer to a module row in plural form (e.g. "Servers", "VPSs", "Reseller Accounts", etc.).
     *
     * @return string The noun used to refer to a module row in plural form
     */
    public function moduleRowNamePlural()
    {
        return Language::_('Centoswebpanel.module_row_plural', true);
    }

    /**
     * Returns a noun used to refer to a module group (e.g. "Server Group").
     *
     * @return string The noun used to refer to a module group
     */
    public function moduleGroupName()
    {
        return Language::_('Centoswebpanel.module_group', true);
    }

    /**
     * Returns the key used to identify the primary field from the set of module row meta fields.
     *
     * @return string The key used to identify the primary field from the set of module row meta fields
     */
    public function moduleRowMetaKey()
    {
        return 'server_name';
    }

    /**
     * Returns an array of available service deligation order methods. The module
     * will determine how each method is defined. For example, the method "first"
     * may be implemented such that it returns the module row with the least number
     * of services assigned to it.
     *
     * @return array An array of order methods in key/value paris where the key is
     *  the type to be stored for the group and value is the name for that option
     * @see Module::selectModuleRow()
     */
    public function getGroupOrderOptions()
    {
        return [
            'roundrobin' => Language::_('Centoswebpanel.order_options.roundrobin', true),
            'first' => Language::_('Centoswebpanel.order_options.first', true)
        ];
    }

    /**
     * Returns all fields used when adding/editing a package, including any
     * javascript to execute when the page is rendered with these fields.
     *
     * @param $vars stdClass A stdClass object representing a set of post fields
     * @return ModuleFields A ModuleFields object, containing the fields to
     *  render as well as any additional HTML markup to include
     */
    public function getPackageFields($vars = null)
    {
        Loader::loadHelpers($this, ['Html']);

        $fields = new ModuleFields();

        // Fetch all packages available for the given server or server group
        $module_row = null;
        if (isset($vars->module_group) && $vars->module_group == '' || $vars->module_group == 'select') {
            if (isset($vars->module_row) && $vars->module_row > 0) {
                $module_row = $this->getModuleRow($vars->module_row);
            } else {
                $rows = $this->getModuleRows();
                if (isset($rows[0])) {
                    $module_row = $rows[0];
                }
                unset($rows);
            }
        } else {
            // Fetch the 1st server from the list of servers in the selected group
            $rows = $this->getModuleRows($vars->module_group);

            if (isset($rows[0])) {
                $module_row = $rows[0];
            }
            unset($rows);
        }

        // Create package label
        $package = $fields->label(Language::_('Centoswebpanel.package_fields.package', true), 'centoswebpanel_package');
        // Create package field and attach to package label
        $package->attach(
            $fields->fieldText(
                'meta[package]',
                $this->Html->ifSet($vars->meta['package']),
                ['id' => 'centoswebpanel_package']
            )
        );
        // Set the label as a field
        $fields->setField($package);

        // Create inode label
        $inode = $fields->label(Language::_('Centoswebpanel.package_fields.inode', true), 'centoswebpanel_inode');
        // Create inode field and attach to inode label
        $inode->attach(
            $fields->fieldText(
                'meta[inode]',
                $this->Html->ifSet($vars->meta['inode']),
                ['id' => 'centoswebpanel_inode']
            )
        );
        // Set the label as a field
        $fields->setField($inode);

        // Create nofile label
        $nofile = $fields->label(Language::_('Centoswebpanel.package_fields.nofile', true), 'centoswebpanel_nofile');
        // Create nofile field and attach to nofile label
        $nofile->attach(
            $fields->fieldText(
                'meta[nofile]',
                $this->Html->ifSet($vars->meta['nofile']),
                ['id' => 'centoswebpanel_nofile']
            )
        );
        // Set the label as a field
        $fields->setField($nofile);

        // Create nproc label
        $nproc = $fields->label(Language::_('Centoswebpanel.package_fields.nproc', true), 'centoswebpanel_nproc');
        // Create nproc field and attach to nproc label
        $nproc->attach(
            $fields->fieldText(
                'meta[nproc]',
                $this->Html->ifSet($vars->meta['nproc']),
                ['id' => 'centoswebpanel_nproc']
            )
        );
        // Set the label as a field
        $fields->setField($nproc);

        return $fields;
    }

    /**
     * Returns an array of key values for fields stored for a module, package,
     * and service under this module, used to substitute those keys with their
     * actual module, package, or service meta values in related emails.
     *
     * @return array A multi-dimensional array of key/value pairs where each key is
     *  one of 'module', 'package', or 'service' and each value is a numerically
     *  indexed array of key values that match meta fields under that category.
     * @see Modules::addModuleRow()
     * @see Modules::editModuleRow()
     * @see Modules::addPackage()
     * @see Modules::editPackage()
     * @see Modules::addService()
     * @see Modules::editService()
     */
    public function getEmailTags()
    {
        return [
            'module' => ['host_name', 'name_servers'],
            'package' => ['package'],
            'service' => ['centoswebpanel_domain', 'centoswebpanel_username', 'centoswebpanel_password']
        ];
    }

    /**
     * Validates input data when attempting to add a package, returns the meta
     * data to save when adding a package. Performs any action required to add
     * the package on the remote server. Sets Input errors on failure,
     * preventing the package from being added.
     *
     * @param array An array of key/value pairs used to add the package
     * @return array A numerically indexed array of meta fields to be stored for this package containing:
     *  - key The key for this meta field
     *  - value The value for this key
     *  - encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function addPackage(array $vars = null)
    {
        // Set rules to validate input data
        $this->Input->setRules($this->getPackageRules($vars));

        // Build meta data to return
        $meta = [];
        if ($this->Input->validates($vars)) {
            // Return all package meta fields
            foreach ($vars['meta'] as $key => $value) {
                $meta[] = [
                    'key' => $key,
                    'value' => $value,
                    'encrypted' => 0
                ];
            }
        }

        return $meta;
    }

    /**
     * Validates input data when attempting to edit a package, returns the meta
     * data to save when editing a package. Performs any action required to edit
     * the package on the remote server. Sets Input errors on failure,
     * preventing the package from being edited.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param array An array of key/value pairs used to edit the package
     * @return array A numerically indexed array of meta fields to be stored for this package containing:
     *  - key The key for this meta field
     *  - value The value for this key
     *  - encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function editPackage($package, array $vars = null)
    {
        // Set rules to validate input data
        $this->Input->setRules($this->getPackageRules($vars));

        // Build meta data to return
        $meta = [];
        if ($this->Input->validates($vars)) {
            // Return all package meta fields
            foreach ($vars['meta'] as $key => $value) {
                $meta[] = [
                    'key' => $key,
                    'value' => $value,
                    'encrypted' => 0
                ];
            }
        }

        return $meta;
    }

    /**
     * Returns the rendered view of the manage module page.
     *
     * @param mixed $module A stdClass object representing the module and its rows
     * @param array $vars An array of post data submitted to or on the manager module
     *  page (used to repopulate fields after an error)
     * @return string HTML content containing information to display when viewing the manager module page
     */
    public function manageModule($module, array &$vars)
    {
        // Load the view into this object, so helpers can be automatically added to the view
        $this->view = new View('manage', 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView('components' . DS . 'modules' . DS . 'centoswebpanel' . DS);

        // Load the helpers required for this view
        Loader::loadHelpers($this, ['Form', 'Html', 'Widget']);

        $this->view->set('module', $module);

        return $this->view->fetch();
    }

    /**
     * Returns the rendered view of the add module row page.
     *
     * @param array $vars An array of post data submitted to or on the add module
     *  row page (used to repopulate fields after an error)
     * @return string HTML content containing information to display when viewing the add module row page
     */
    public function manageAddRow(array &$vars)
    {
        // Load the view into this object, so helpers can be automatically added to the view
        $this->view = new View('add_row', 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView('components' . DS . 'modules' . DS . 'centoswebpanel' . DS);

        // Load the helpers required for this view
        Loader::loadHelpers($this, ['Form', 'Html', 'Widget']);

        // Set unspecified checkboxes
        if (!empty($vars)) {
            if (empty($vars['use_ssl'])) {
                $vars['use_ssl'] = 'false';
            }
        }

        $this->view->set('vars', (object) $vars);

        return $this->view->fetch();
    }

    /**
     * Returns the rendered view of the edit module row page.
     *
     * @param stdClass $module_row The stdClass representation of the existing module row
     * @param array $vars An array of post data submitted to or on the edit
     *  module row page (used to repopulate fields after an error)
     * @return string HTML content containing information to display when viewing the edit module row page
     */
    public function manageEditRow($module_row, array &$vars)
    {
        // Load the view into this object, so helpers can be automatically added to the view
        $this->view = new View('edit_row', 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView('components' . DS . 'modules' . DS . 'centoswebpanel' . DS);

        // Load the helpers required for this view
        Loader::loadHelpers($this, ['Form', 'Html', 'Widget']);

        if (empty($vars)) {
            $vars = $module_row->meta;
        } else {
            // Set unspecified checkboxes
            if (empty($vars['use_ssl'])) {
                $vars['use_ssl'] = 'false';
            }
        }

        $this->view->set('vars', (object) $vars);

        return $this->view->fetch();
    }

    /**
     * Adds the module row on the remote server. Sets Input errors on failure,
     * preventing the row from being added. Returns a set of data, which may be
     * a subset of $vars, that is stored for this module row.
     *
     * @param array $vars An array of module info to add
     * @return array A numerically indexed array of meta fields for the module row containing:
     *  - key The key for this meta field
     *  - value The value for this key
     *  - encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     */
    public function addModuleRow(array &$vars)
    {
        $meta_fields = ['server_name', 'host_name', 'api_key',
            'use_ssl', 'account_limit', 'name_servers', 'notes'];
        $encrypted_fields = ['api_key'];

        // Set unspecified checkboxes
        if (empty($vars['use_ssl'])) {
            $vars['use_ssl'] = 'false';
        }

        $this->Input->setRules($this->getRowRules($vars));

        // Validate module row
        if ($this->Input->validates($vars)) {
            // Build the meta data for this row
            $meta = [];
            foreach ($vars as $key => $value) {
                if (in_array($key, $meta_fields)) {
                    $meta[] = [
                        'key'=>$key,
                        'value'=>$value,
                        'encrypted'=>in_array($key, $encrypted_fields) ? 1 : 0
                    ];
                }
            }

            return $meta;
        }
    }

    /**
     * Edits the module row on the remote server. Sets Input errors on failure,
     * preventing the row from being updated. Returns a set of data, which may be
     * a subset of $vars, that is stored for this module row.
     *
     * @param stdClass $module_row The stdClass representation of the existing module row
     * @param array $vars An array of module info to update
     * @return array A numerically indexed array of meta fields for the module row containing:
     *  - key The key for this meta field
     *  - value The value for this key
     *  - encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     */
    public function editModuleRow($module_row, array &$vars)
    {
        $meta_fields = ['server_name', 'host_name', 'api_key',
            'use_ssl', 'account_limit', 'account_count', 'name_servers', 'notes'];
        $encrypted_fields = ['api_key'];

        // Set unspecified checkboxes
        if (empty($vars['use_ssl'])) {
            $vars['use_ssl'] = 'false';
        }

        $this->Input->setRules($this->getRowRules($vars));

        // Validate module row
        if ($this->Input->validates($vars)) {
            // Build the meta data for this row
            $meta = [];
            foreach ($vars as $key => $value) {
                if (in_array($key, $meta_fields)) {
                    $meta[] = [
                        'key'=>$key,
                        'value'=>$value,
                        'encrypted'=>in_array($key, $encrypted_fields) ? 1 : 0
                    ];
                }
            }

            return $meta;
        }
    }

    /**
     * Deletes the module row on the remote server. Sets Input errors on failure,
     * preventing the row from being deleted.
     *
     * @param stdClass $module_row The stdClass representation of the existing module row
     */
    public function deleteModuleRow($module_row)
    {
        // Nothing to do
    }

    /**
     * Returns the value used to identify a particular service.
     *
     * @param stdClass $service A stdClass object representing the service
     * @return string A value used to identify this service amongst other similar services
     */
    public function getServiceName($service)
    {
        foreach ($service->fields as $field) {
            if ($field->key == 'centoswebpanel_domain') {
                return $field->value;
            }
        }

        return null;
    }

    /**
     * Returns the value used to identify a particular package service which has
     * not yet been made into a service. This may be used to uniquely identify
     * an uncreated services of the same package (i.e. in an order form checkout).
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param array $vars An array of user supplied info to satisfy the request
     * @return string The value used to identify this package service
     * @see Module::getServiceName()
     */
    public function getPackageServiceName($package, array $vars = null)
    {
        if (isset($vars['centoswebpanel_domain'])) {
            return $vars['centoswebpanel_domain'];
        }

        return null;
    }

    /**
     * Returns all fields to display to an admin attempting to add a service with the module.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param $vars stdClass A stdClass object representing a set of post fields
     * @return ModuleFields A ModuleFields object, containg the fields to render
     *  as well as any additional HTML markup to include
     */
    public function getAdminAddFields($package, $vars = null)
    {
        Loader::loadHelpers($this, ['Html']);

        $fields = new ModuleFields();

        // Create domain label
        $domain = $fields->label(Language::_('Centoswebpanel.service_field.domain', true), 'centoswebpanel_domain');
        // Create domain field and attach to domain label
        $domain->attach(
            $fields->fieldText('centoswebpanel_domain', $this->Html->ifSet($vars->centoswebpanel_domain), ['id'=>'centoswebpanel_domain'])
        );
        // Set the label as a field
        $fields->setField($domain);

        // Create username label
        $username = $fields->label(Language::_('Centoswebpanel.service_field.username', true), 'centoswebpanel_username');
        // Create username field and attach to username label
        $username->attach(
            $fields->fieldText('centoswebpanel_username', $this->Html->ifSet($vars->centoswebpanel_username), ['id'=>'centoswebpanel_username'])
        );
        // Add tooltip
        $tooltip = $fields->tooltip(Language::_('Centoswebpanel.service_field.tooltip.username', true));
        $username->attach($tooltip);
        // Set the label as a field
        $fields->setField($username);

        // Create password label
        $password = $fields->label(Language::_('Centoswebpanel.service_field.password', true), 'centoswebpanel_password');
        // Create password field and attach to password label
        $password->attach(
            $fields->fieldPassword(
                'centoswebpanel_password',
                ['id' => 'centoswebpanel_password', 'value' => $this->Html->ifSet($vars->centoswebpanel_password)]
            )
        );
        // Add tooltip
        $tooltip = $fields->tooltip(Language::_('Centoswebpanel.service_field.tooltip.password', true));
        $password->attach($tooltip);
        // Set the label as a field
        $fields->setField($password);

        return $fields;
    }

    /**
     * Returns all fields to display to a client attempting to add a service with the module.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param $vars stdClass A stdClass object representing a set of post fields
     * @return ModuleFields A ModuleFields object, containg the fields to render as well
     *  as any additional HTML markup to include
     */
    public function getClientAddFields($package, $vars = null)
    {
        Loader::loadHelpers($this, ['Html']);

        $fields = new ModuleFields();

        // Create domain label
        $domain = $fields->label(Language::_('Centoswebpanel.service_field.domain', true), 'centoswebpanel_domain');
        // Create domain field and attach to domain label
        $domain->attach(
            $fields->fieldText(
                'centoswebpanel_domain',
                $this->Html->ifSet($vars->centoswebpanel_domain, $this->Html->ifSet($vars->domain)),
                ['id' => 'centoswebpanel_domain']
            )
        );
        // Set the label as a field
        $fields->setField($domain);

        return $fields;
    }

    /**
     * Returns all fields to display to an admin attempting to edit a service with the module.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param $vars stdClass A stdClass object representing a set of post fields
     * @return ModuleFields A ModuleFields object, containg the fields to render as
     *  well as any additional HTML markup to include
     */
    public function getAdminEditFields($package, $vars = null)
    {
        Loader::loadHelpers($this, ['Html']);

        $fields = new ModuleFields();

        // Create domain label
        $domain = $fields->label(Language::_('Centoswebpanel.service_field.domain', true), 'centoswebpanel_domain');
        // Create domain field and attach to domain label
        $domain->attach(
            $fields->fieldText('centoswebpanel_domain', $this->Html->ifSet($vars->centoswebpanel_domain), ['id'=>'centoswebpanel_domain'])
        );
        // Set the label as a field
        $fields->setField($domain);

        // Create username label
        $username = $fields->label(Language::_('Centoswebpanel.service_field.username', true), 'centoswebpanel_username');
        // Create username field and attach to username label
        $username->attach(
            $fields->fieldText('centoswebpanel_username', $this->Html->ifSet($vars->centoswebpanel_username), ['id'=>'centoswebpanel_username'])
        );
        // Set the label as a field
        $fields->setField($username);

        // Create password label
        $password = $fields->label(Language::_('Centoswebpanel.service_field.password', true), 'centoswebpanel_password');
        // Create password field and attach to password label
        $password->attach(
            $fields->fieldPassword(
                'centoswebpanel_password',
                ['id' => 'centoswebpanel_password', 'value' => $this->Html->ifSet($vars->centoswebpanel_password)]
            )
        );
        // Set the label as a field
        $fields->setField($password);

        return $fields;
    }

    /**
     * Attempts to validate service info. This is the top-level error checking method. Sets Input errors on failure.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param array $vars An array of user supplied info to satisfy the request
     * @param bool $edit True if this is an edit, false otherwise
     * @return bool True if the service validates, false otherwise. Sets Input errors when false.
     */
    public function validateService($package, array $vars = null, $edit = false)
    {
        $rules = [
            'centoswebpanel_domain' => [
                'format' => [
                    'rule' => [[$this, 'validateHostName']],
                    'message' => Language::_('Centoswebpanel.!error.centoswebpanel_domain.format', true)
                ]
            ],
            'centoswebpanel_username' => [
                'format' => [
                    'if_set' => true,
                    'rule' => ['matches', '/^[a-z]([a-z0-9])*$/i'],
                    'message' => Language::_('Centoswebpanel.!error.centoswebpanel_username.format', true)
                ],
                'length' => [
                    'if_set' => true,
                    'rule' => ['betweenLength', 1, 16],
                    'message' => Language::_('Centoswebpanel.!error.centoswebpanel_username.length', true)
                ]
            ],
            'centoswebpanel_password' => [
                'valid' => [
                    'if_set' => true,
                    'rule' => ['isPassword', 8],
                    'message' => Language::_('Centoswebpanel.!error.centoswebpanel_password.valid', true),
                    'last' => true
                ],
            ]
        ];

        // Set the values that may be empty
        $empty_values = ['centoswebpanel_username', 'centoswebpanel_password'];

        if ($edit) {
            // If this is an edit and no password given then don't evaluate password
            // since it won't be updated
            if (!array_key_exists('centoswebpanel_password', $vars) || $vars['centoswebpanel_password'] == '') {
                unset($rules['centoswebpanel_password']);
            }
        }

        // Remove rules on empty fields
        foreach ($empty_values as $value) {
            if (empty($vars[$value])) {
                unset($rules[$value]);
            }
        }

        $this->Input->setRules($rules);

        return $this->Input->validates($vars);
    }

    /**
     * Adds the service to the remote server. Sets Input errors on failure,
     * preventing the service from being added.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param array $vars An array of user supplied info to satisfy the request
     * @param stdClass $parent_package A stdClass object representing the parent
     *  service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent
     *  service of the service being added (if the current service is an addon service
     *  service and parent service has already been provisioned)
     * @param string $status The status of the service being added. These include:
     *  - active
     *  - canceled
     *  - pending
     *  - suspended
     * @return array A numerically indexed array of meta fields to be stored for this service containing:
     *  - key The key for this meta field
     *  - value The value for this key
     *  - encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function addService($package, array $vars = null, $parent_package = null, $parent_service = null, $status = 'pending')
    {
        $row = $this->getModuleRow();

        if (!$row) {
            $this->Input->setErrors(
                ['module_row' => ['missing' => Language::_('Centoswebpanel.!error.module_row.missing', true)]]
            );

            return;
        }

        $api = $this->getApi($row->meta->host_name, $row->meta->api_key, $row->meta->use_ssl);

        // Generate username/password
        if (array_key_exists('centoswebpanel_domain', $vars)) {
            Loader::loadModels($this, ['Clients']);

            // Generate a username
            if (empty($vars['centoswebpanel_username'])) {
                $vars['centoswebpanel_username'] = $this->generateUsername($vars['centoswebpanel_domain']);
            }

            // Generate a password
            if (empty($vars['centoswebpanel_password'])) {
                $vars['centoswebpanel_password'] = $this->generatePassword();
            }

            // Get client's contact email address
            if (isset($vars['client_id']) && ($client = $this->Clients->get($vars['client_id'], false))) {
                $vars['centoswebpanel_email'] = $client->email;
            }
        }

        $params = $this->getFieldsFromInput((array) $vars, $package);

        $this->validateService($package, $vars);

        if ($this->Input->errors()) {
            return;
        }

        // Only provision the service if 'use_module' is true
        if ($vars['use_module'] == 'true') {
            // Create CentOS WebPanel account
            $masked_params = $params;
            $masked_params['password'] = '***';
            $this->log($row->meta->host_name . '|account_new', serialize($masked_params), 'input', true);
            unset($masked_params);
            $result = $this->parseResponse($api->createAccount($params));

            if ($this->Input->errors()) {
                return;
            }

            // Update the number of accounts on the server
            $this->updateAccountCount($row);
        }

        // Return service fields
        return [
            [
                'key' => 'centoswebpanel_domain',
                'value' => $vars['centoswebpanel_domain'],
                'encrypted' => 0
            ],
            [
                'key' => 'centoswebpanel_username',
                'value' => $vars['centoswebpanel_username'],
                'encrypted' => 0
            ],
            [
                'key' => 'centoswebpanel_password',
                'value' => $vars['centoswebpanel_password'],
                'encrypted' => 1
            ]
        ];
    }

    /**
     * Edits the service on the remote server. Sets Input errors on failure,
     * preventing the service from being edited.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param array $vars An array of user supplied info to satisfy the request
     * @param stdClass $parent_package A stdClass object representing the parent
     *  service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent
     *  service of the service being edited (if the current service is an addon service)
     * @return array A numerically indexed array of meta fields to be stored for this service containing:
     *  - key The key for this meta field
     *  - value The value for this key
     *  - encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function editService($package, $service, array $vars = null, $parent_package = null, $parent_service = null)
    {
        $row = $this->getModuleRow();
        $api = $this->getApi($row->meta->host_name, $row->meta->api_key, $row->meta->use_ssl);

        $this->validateService($package, $vars, true);

        if ($this->Input->errors()) {
            return;
        }

        $service_fields = $this->serviceFieldsToObject($service->fields);

        // Remove password if not being updated
        if (isset($vars['centoswebpanel_password']) && $vars['centoswebpanel_password'] == '') {
            unset($vars['centoswebpanel_password']);
        }

        // Only update the service if 'use_module' is true
        if ($vars['use_module'] == 'true') {
            // Unfortunately the CentOS WebPanel API does not support updating
            // any of the account fields, so nothing to do here. :(
            // We will only update the fields locally.
        }

        // Set fields to update locally
        $fields = ['centoswebpanel_domain', 'centoswebpanel_username', 'centoswebpanel_password'];
        foreach ($fields as $field) {
            if (property_exists($service_fields, $field) && isset($vars[$field])) {
                $service_fields->{$field} = $vars[$field];
            }
        }

        // Return all the service fields
        $fields = [];
        $encrypted_fields = ['centoswebpanel_password'];
        foreach ($service_fields as $key => $value) {
            $fields[] = ['key' => $key, 'value' => $value, 'encrypted' => (in_array($key, $encrypted_fields) ? 1 : 0)];
        }

        return $fields;
    }

    /**
     * Suspends the service on the remote server. Sets Input errors on failure,
     * preventing the service from being suspended.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param stdClass $parent_package A stdClass object representing the parent
     *  service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent
     *  service of the service being suspended (if the current service is an addon service)
     * @return mixed null to maintain the existing meta fields or a numerically
     *  indexed array of meta fields to be stored for this service containing:
     *  - key The key for this meta field
     *  - value The value for this key
     *  - encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function suspendService($package, $service, $parent_package = null, $parent_service = null)
    {
        if (($row = $this->getModuleRow())) {
            $api = $this->getApi($row->meta->host_name, $row->meta->api_key, $row->meta->use_ssl);

            $service_fields = $this->serviceFieldsToObject($service->fields);

            // Suspend CentOS WebPanel account
            $this->log($row->meta->host_name . '|account_suspend', serialize($service_fields->centoswebpanel_username), 'input', true);
            $this->parseResponse($api->suspendAccount($service_fields->centoswebpanel_username));
        }

        return null;
    }

    /**
     * Unsuspends the service on the remote server. Sets Input errors on failure,
     * preventing the service from being unsuspended.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param stdClass $parent_package A stdClass object representing the parent
     *  service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent
     *  service of the service being unsuspended (if the current service is an addon service)
     * @return mixed null to maintain the existing meta fields or a numerically
     *  indexed array of meta fields to be stored for this service containing:
     *  - key The key for this meta field
     *  - value The value for this key
     *  - encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function unsuspendService($package, $service, $parent_package = null, $parent_service = null)
    {
        if (($row = $this->getModuleRow())) {
            $api = $this->getApi($row->meta->host_name, $row->meta->api_key, $row->meta->use_ssl);

            $service_fields = $this->serviceFieldsToObject($service->fields);

            // Unsuspend CentOS WebPanel account
            $this->log($row->meta->host_name . '|account_unsuspend', serialize($service_fields->centoswebpanel_username), 'input', true);
            $this->parseResponse($api->unsuspendAccount($service_fields->centoswebpanel_username));
        }

        return null;
    }

    /**
     * Cancels the service on the remote server. Sets Input errors on failure,
     * preventing the service from being canceled.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param stdClass $parent_package A stdClass object representing the parent
     *  service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent
     *  service of the service being canceled (if the current service is an addon service)
     * @return mixed null to maintain the existing meta fields or a numerically
     *  indexed array of meta fields to be stored for this service containing:
     *  - key The key for this meta field
     *  - value The value for this key
     *  - encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function cancelService($package, $service, $parent_package = null, $parent_service = null)
    {
        if (($row = $this->getModuleRow())) {
            $api = $this->getApi($row->meta->host_name, $row->meta->api_key, $row->meta->use_ssl);

            $service_fields = $this->serviceFieldsToObject($service->fields);

            // Delete CentOS WebPanel account
            $this->log($row->meta->host_name . '|account_remove', serialize($service_fields->centoswebpanel_username), 'input', true);
            $this->parseResponse($api->removeAccount($service_fields->centoswebpanel_username));

            // Update the number of accounts on the server
            $this->updateAccountCount($row, false);
        }

        return null;
    }

    /**
     * Updates the package for the service on the remote server. Sets Input
     * errors on failure, preventing the service's package from being changed.
     *
     * @param stdClass $package_from A stdClass object representing the current package
     * @param stdClass $package_to A stdClass object representing the new package
     * @param stdClass $service A stdClass object representing the current service
     * @param stdClass $parent_package A stdClass object representing the parent
     *  service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent
     *  service of the service being changed (if the current service is an addon service)
     * @return mixed null to maintain the existing meta fields or a numerically
     *  indexed array of meta fields to be stored for this service containing:
     *  - key The key for this meta field
     *  - value The value for this key
     *  - encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function changeServicePackage($package_from, $package_to, $service, $parent_package = null, $parent_service = null)
    {
        // Nothing to do
    }

    /**
     * Fetches the HTML content to display when viewing the service info in the
     * admin interface.
     *
     * @param stdClass $service A stdClass object representing the service
     * @param stdClass $package A stdClass object representing the service's package
     * @return string HTML content containing information to display when viewing the service info
     */
    public function getAdminServiceInfo($service, $package)
    {
        $row = $this->getModuleRow();

        // Load the view into this object, so helpers can be automatically added to the view
        $this->view = new View('admin_service_info', 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView('components' . DS . 'modules' . DS . 'centoswebpanel' . DS);

        // Load the helpers required for this view
        Loader::loadHelpers($this, ['Form', 'Html']);

        $this->view->set('module_row', $row);
        $this->view->set('package', $package);
        $this->view->set('service', $service);
        $this->view->set('service_fields', $this->serviceFieldsToObject($service->fields));

        return $this->view->fetch();
    }

    /**
     * Fetches the HTML content to display when viewing the service info in the
     * client interface.
     *
     * @param stdClass $service A stdClass object representing the service
     * @param stdClass $package A stdClass object representing the service's package
     * @return string HTML content containing information to display when viewing the service info
     */
    public function getClientServiceInfo($service, $package)
    {
        $row = $this->getModuleRow();

        // Load the view into this object, so helpers can be automatically added to the view
        $this->view = new View('client_service_info', 'default');
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView('components' . DS . 'modules' . DS . 'centoswebpanel' . DS);

        // Load the helpers required for this view
        Loader::loadHelpers($this, ['Form', 'Html']);

        $this->view->set('module_row', $row);
        $this->view->set('package', $package);
        $this->view->set('service', $service);
        $this->view->set('service_fields', $this->serviceFieldsToObject($service->fields));

        return $this->view->fetch();
    }

    /**
     * Firewall tab.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param array $get Any GET parameters
     * @param array $post Any POST parameters
     * @param array $files Any FILES parameters
     * @return string The string representing the contents of this tab
     */
    public function tabFirewall($package, $service, array $get = null, array $post = null, array $files = null)
    {
        $row = $this->getModuleRow();
        $api = $this->getApi($row->meta->host_name, $row->meta->api_key, $row->meta->use_ssl);

        $this->view = new View('tab_firewall', 'default');

        // Load the helpers required for this view
        Loader::loadHelpers($this, ['Form', 'Html']);

        // Unblock IP address
        if (!empty($post)) {
            if (isset($post['unblock'])) {
                $this->log($row->meta->host_name . '|unblock_ip', serialize($post['ip_address']), 'input', true);
                $result = $this->parseResponse($api->unblockIp($post['ip_address']));
            }
        }

        // Get client IP address
        $ip_address = $api->getClientIp();

        $this->view->set('ip_address', $ip_address);
        $this->view->set('vars', (object) $this->Html->ifSet($post));

        $this->view->setDefaultView('components' . DS . 'modules' . DS . 'centoswebpanel' . DS);

        return $this->view->fetch();
    }

    /**
     * Client firewall tab.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param array $get Any GET parameters
     * @param array $post Any POST parameters
     * @param array $files Any FILES parameters
     * @return string The string representing the contents of this tab
     */
    public function tabClientFirewall($package, $service, array $get = null, array $post = null, array $files = null)
    {
        $row = $this->getModuleRow();
        $api = $this->getApi($row->meta->host_name, $row->meta->api_key, $row->meta->use_ssl);

        $this->view = new View('tab_client_firewall', 'default');

        // Load the helpers required for this view
        Loader::loadHelpers($this, ['Form', 'Html']);

        // Unblock IP address
        if (!empty($post)) {
            if (isset($post['unblock'])) {
                $this->log($row->meta->host_name . '|unblock_ip', serialize($post['ip_address']), 'input', true);
                $result = $this->parseResponse($api->unblockIp($post['ip_address']));
            }
        }

        // Get client IP address
        $ip_address = $api->getClientIp();

        $this->view->set('ip_address', $ip_address);
        $this->view->set('vars', (object) $this->Html->ifSet($post));

        $this->view->setDefaultView('components' . DS . 'modules' . DS . 'centoswebpanel' . DS);

        return $this->view->fetch();
    }

    /**
     * Validates that the given hostname is valid.
     *
     * @param string $host_name The host name to validate
     * @return bool True if the hostname is valid, false otherwise
     */
    public function validateHostName($host_name)
    {
        if (strlen($host_name) > 255) {
            return false;
        }

        return $this->Input->matches(
            $host_name,
            "/^([a-z0-9]|[a-z0-9][a-z0-9\-]{0,61}[a-z0-9])(\.([a-z0-9]|[a-z0-9][a-z0-9\-]{0,61}[a-z0-9]))+$/"
        );
    }

    /**
     * Validates that at least 2 name servers are set in the given array of name servers.
     *
     * @param array $name_servers An array of name servers
     * @return bool True if the array count is >= 2, false otherwise
     */
    public function validateNameServerCount($name_servers)
    {
        if (is_array($name_servers) && count($name_servers) >= 2) {
            return true;
        }

        return false;
    }

    /**
     * Validates that the nameservers given are formatted correctly.
     *
     * @param array $name_servers An array of name servers
     * @return bool True if every name server is formatted correctly, false otherwise
     */
    public function validateNameServers($name_servers)
    {
        if (is_array($name_servers)) {
            foreach ($name_servers as $name_server) {
                if (!$this->validateHostName($name_server)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Retrieves the accounts on the server.
     *
     * @param stdClass $api The CentOS WebPanel API
     * @return mixed The number of CentOS WebPanel accounts on the server, or false on error
     */
    private function getAccountCount($api)
    {
        $accounts = false;

        // Get module row meta
        $vars = $this->ModuleManager->getRowMeta($module_row->id);

        // Update account count
        $accounts = (int) $vars->account_count;

        return $accounts;
    }

    /**
     * Updates the module row meta number of accounts.
     *
     * @param stdClass $module_row A stdClass object representing a single server
     * @param mixed $increase
     */
    private function updateAccountCount($module_row, $increase = true)
    {
        // Get module row meta
        $vars = $this->ModuleManager->getRowMeta($module_row->id);

        // Update account count
        $count = (int) $vars->account_count;

        if ($increase) {
            $vars->account_count = $count + 1;
        } else {
            $vars->account_count = $count - 1;
        }

        if ($vars->account_count < 0) {
            $vars->account_count = 0;
        }

        // Update the module row account list
        $vars = (array) $vars;
        $this->ModuleManager->editRow($module_row->id, $vars);
    }

    /**
     * Validates whether or not the connection details are valid by attempting to fetch
     * the number of accounts that currently reside on the server.
     *
     * @param mixed $api_key
     * @param mixed $hostname
     * @param mixed $use_ssl
     * @return bool True if the connection is valid, false otherwise
     */
    public function validateConnection($api_key, $hostname, $use_ssl)
    {
        try {
            $api = $this->getApi($hostname, $api_key, $use_ssl);
            $response = $api->unblockIp($_SERVER['SERVER_ADDR']);

            if (!empty($response)) {
                return true;
            }
        } catch (Exception $e) {
            // Trap any errors encountered, could not validate connection
        }

        return false;
    }

    /**
     * Generates a username from the given host name.
     *
     * @param string $host_name The host name to use to generate the username
     * @return string The username generated from the given hostname
     */
    private function generateUsername($host_name)
    {
        // Remove everything except letters and numbers from the domain
        // ensure no number appears in the beginning
        $username = ltrim(preg_replace('/[^a-z0-9]/i', '', $host_name), '0123456789');

        $length = strlen($username);
        $pool = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $pool_size = strlen($pool);

        if ($length < 5) {
            for ($i=$length; $i < 8; $i++) {
                $username .= substr($pool, mt_rand(0, $pool_size - 1), 1);
            }
            $length = strlen($username);
        }

        $username = substr($username, 0, min($length, 8));

        // Check for an existing user account
        $row = $this->getModuleRow();

        if ($row) {
            $api = $this->getApi($row->meta->host_name, $row->meta->api_key, $row->meta->use_ssl);
        }

        // Username exists, create another instead
        if (!$api->accountExists($username)) {
            for ($i=0; $i < (int) str_repeat(9, $account_matching_characters); $i++) {
                $new_username = substr($username, 0, -$account_matching_characters) . $i;
                if (!array_key_exists($new_username, $accounts)) {
                    $username = $new_username;
                    break;
                }
            }
        }

        return $username;
    }

    /**
     * Generates a password.
     *
     * @param int $min_length The minimum character length for the password (5 or larger)
     * @param int $max_length The maximum character length for the password (14 or fewer)
     * @return string The generated password
     */
    private function generatePassword($min_length = 10, $max_length = 14)
    {
        $pool = 'abcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        $pool_size = strlen($pool);
        $length = mt_rand(max($min_length, 5), min($max_length, 14));
        $password = '';

        for ($i=0; $i < $length; $i++) {
            $password .= substr($pool, mt_rand(0, $pool_size - 1), 1);
        }

        return $password;
    }

    /**
     * Returns an array of service field to set for the service using the given input.
     *
     * @param array $vars An array of key/value input pairs
     * @param stdClass $package A stdClass object representing the package for the service
     * @return array An array of key/value pairs representing service fields
     */
    private function getFieldsFromInput(array $vars, $package)
    {
        $fields = [
            'domain' => isset($vars['centoswebpanel_domain']) ? $vars['centoswebpanel_domain'] : null,
            'username' => isset($vars['centoswebpanel_username']) ? $vars['centoswebpanel_username'] : null,
            'password' => isset($vars['centoswebpanel_password']) ? $vars['centoswebpanel_password'] : null,
            'email' => isset($vars['centoswebpanel_email']) ? $vars['centoswebpanel_email'] : null,
            'package' => $package->meta->package,
            'inode' => $package->meta->inode,
            'nofile' => $package->meta->nofile,
            'nproc' => $package->meta->nproc
        ];

        return $fields;
    }

    /**
     * Parses the response from the API into a stdClass object.
     *
     * @param string $response The response from the API
     * @return stdClass A stdClass object representing the response, void if the response was an error
     */
    private function parseResponse($response)
    {
        $row = $this->getModuleRow();

        $success = true;

        // Set internal error
        if (!$response) {
            $this->Input->setErrors(['api' => ['internal' => Language::_('Centoswebpanel.!error.api.internal', true)]]);
            $success = false;
        }

        // Only some API requests return status, so only use it if its available
        if (!$response['success']) {
            $this->Input->setErrors(['api' => ['result' => $response['message']]]);
            $success = false;
        }

        // Log the response
        $this->log($row->meta->host_name, serialize($response), 'output', $success);

        // Return if any errors encountered
        if (!$success) {
            return;
        }

        return $response;
    }

    /**
     * Initializes the CentoswebpanelApi and returns an instance of that object.
     *
     * @param string $hostname The host to the CentOS WebPanel server
     * @param string $api_key The remote api key
     * @param mixed $use_ssl
     * @return CentoswebpanelApi The CentoswebpanelApi instance
     */
    private function getApi($hostname, $api_key, $use_ssl = true)
    {
        Loader::load(dirname(__FILE__) . DS . 'apis' . DS . 'centoswebpanel_api.php');

        $api = new CentoswebpanelApi($hostname, $api_key, $use_ssl);

        return $api;
    }

    /**
     * Builds and returns the rules required to add/edit a module row (e.g. server).
     *
     * @param array $vars An array of key/value data pairs
     * @return array An array of Input rules suitable for Input::setRules()
     */
    private function getRowRules(&$vars)
    {
        $rules = [
            'server_name'=>[
                'valid'=>[
                    'rule'=>'isEmpty',
                    'negate'=>true,
                    'message'=>Language::_('Centoswebpanel.!error.server_name_valid', true)
                ]
            ],
            'host_name'=>[
                'valid'=>[
                    'rule'=>[[$this, 'validateHostName']],
                    'message'=>Language::_('Centoswebpanel.!error.host_name_valid', true)
                ]
            ],
            'api_key'=>[
                'valid'=>[
                    'last'=>true,
                    'rule'=>'isEmpty',
                    'negate'=>true,
                    'message'=>Language::_('Centoswebpanel.!error.remote_api_key_valid', true)
                ],
                'valid_connection'=>[
                    'rule' => [
                        [$this, 'validateConnection'],
                        $vars['host_name'],
                        $vars['use_ssl'],
                    ],
                    'message'=>Language::_('Centoswebpanel.!error.remote_api_key_valid_connection', true)
                ]
            ],
            'account_limit'=>[
                'valid'=>[
                    'rule'=>['matches', '/^([0-9]+)?$/'],
                    'message'=>Language::_('Centoswebpanel.!error.account_limit_valid', true)
                ]
            ],
            'name_servers'=>[
                'count'=>[
                    'rule'=>[[$this, 'validateNameServerCount']],
                    'message'=>Language::_('Centoswebpanel.!error.name_servers_count', true)
                ],
                'valid'=>[
                    'rule'=>[[$this, 'validateNameServers']],
                    'message'=>Language::_('Centoswebpanel.!error.name_servers_valid', true)
                ]
            ]
        ];

        return $rules;
    }

    /**
     * Builds and returns rules required to be validated when adding/editing a package.
     *
     * @param array $vars An array of key/value data pairs
     * @return array An array of Input rules suitable for Input::setRules()
     */
    private function getPackageRules($vars)
    {
        $rules = [
            'meta[package]' => [
                'empty' => [
                    'rule' => 'isEmpty',
                    'negate' => true,
                    'message' => Language::_('Centoswebpanel.!error.meta[package].empty', true) // package must be given
                ]
            ],
            'meta[inode]' => [
                'valid' => [
                    'rule' => ['matches', '/^([0-9]+)$/'],
                    'message' => Language::_('Centoswebpanel.!error.meta[inode].valid', true),
                ]
            ],
            'meta[nofile]' => [
                'valid' => [
                    'rule' => ['matches', '/^([0-9]+)$/'],
                    'message' => Language::_('Centoswebpanel.!error.meta[nofile].valid', true),
                ]
            ],
            'meta[nproc]' => [
                'valid' => [
                    'rule' => ['matches', '/^([0-9]+)$/'],
                    'message' => Language::_('Centoswebpanel.!error.meta[nproc].valid', true),
                ]
            ]
        ];

        return $rules;
    }
}
