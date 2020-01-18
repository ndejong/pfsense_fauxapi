<?php

namespace fauxapi\v1;

include 'fauxapi_pfsense_interface_alias.priv.inc';
trait network_address_aliases
{
    /**
     * network_address_aliases_get()
     * 
     * @return array
     */
    public function network_address_aliases_get()
    {
        global $config;
        fauxApiLogger::debug(__METHOD__);
        return $config["aliases"];
    }


    /**
     * network_address_aliases_create()
     * @param string $name - alias name
     * @param string $type - alias type, supports "network" only
     * @param array $cidr_addresses - alias CIDRAddress {"address": "1.2.3.4/32", "details":"message"}
     * @param string $descr - alias descr, use for UI item.
     * @return array
     */
    public function network_address_aliases_create($name, $type, $cidr_addresses, $descr = "Added by fauxapi")
    {
        global $config;
        fauxApiLogger::debug(__METHOD__, array(
            'name' => $name, 'type' => $type, 'cidr_addresses' => $cidr_addresses, 'descr' => $descr
        ));
        //TODO: change this if we need address_alias type to other.
        if ($type != "network") {
            $error_message = "not supported aliases type for now";
            $error_data = array('type' => $type);
            fauxApiLogger::error($error_message, $error_data);
            throw new \Exception($error_message);
        }
        $error_message = fauxApiInterfaceAliasTools::is_valid_network_address_alias_name($name);
        if ($error_message !== NULL) {
            $error_data = array('name' => $name);
            fauxApiLogger::error($error_message, $error_data);
            throw new \Exception($error_message);
        }
        if (!is_array($cidr_addresses) || !$this->is_numeric_array($cidr_addresses) || count($cidr_addresses) == 0) {
            $error_message = "must be a object array";
            $error_data = array('cidr_addresses' => $cidr_addresses);
            fauxApiLogger::error($error_message, $error_data);
            throw new \Exception($error_message);
        }
        $result = fauxApiInterfaceAliasTools::parse_cidr_addresslist_to_config($cidr_addresses);
        # this shell saves to config.
        $pconfig['name'] = $name;
        $pconfig['descr'] = $descr;
        $pconfig['type'] = $type;
        $pconfig['address'] = $result["address"];
        $pconfig['detail'] = $result["detail"];
        init_config_arr(array('aliases', 'alias'));
        $currsize = count($config['aliases']['alias']);
        $config["aliases"]['alias'][$currsize] = $pconfig;
        if (!fauxApiInterfaceAliasTools::write_config_aliases()) {
            return NULL;
        }
        return $config["aliases"];
    }

    /**
     * network_address_aliases_update()
     * 
     * @param string $name - which alias to modify
     * @param string $type - alias type, supports "network" only
     * @param array $cidr_addresses - alias CIDRAddress {"address": "1.2.3.4/32", "details":"message"}
     * @param string $descr - alias descr, use for UI item.
     * @return array
     */
    public function network_address_aliases_update($name, $type, $cidr_addresses, $descr = "Added by fauxapi")
    {
        global $config;
        fauxApiLogger::debug(__METHOD__, array(
            'name' => $name, 'type' => $type, 'cidr_addresses' => $cidr_addresses, 'descr' => $descr
        ));
        $pconfig=NULL;
        for ($id = 0; $id < count($config["aliases"]["alias"]); $id+=1) {
            $cfgitem = $config["aliases"]["alias"][$id];
            if ($cfgitem["name"] == $name){
                $pconfig=&$config["aliases"]["alias"][$id];
            }
        }
        if ($pconfig== NULL){
            //not find
            $error_message = "not find name";
            $error_data = array('name' => $name);
            fauxApiLogger::error($error_message, $error_data);
            throw new \Exception($error_message);
        }
        if ($type != "network") {
            $error_message = "can support type network only for now";
            $error_data = array('type' => $type);
            fauxApiLogger::error($error_message, $error_data);
            throw new \Exception($error_message);
        }
        $result = fauxApiInterfaceAliasTools::parse_cidr_addresslist_to_config($cidr_addresses);
        # this shell saves to config.
        $pconfig['name'] = $name;
        $pconfig['descr'] = $descr;
        $pconfig['type'] = $type;
        $pconfig['address'] = $result["address"];
        $pconfig['detail'] = $result["detail"];
        if (!fauxApiInterfaceAliasTools::write_config_aliases()) {
            return NULL;
        }

        return $config["aliases"];
    }
    /**
     * network_address_aliases_delete()
     * 
     * @param string $name - which alias to modify
     * @return array
     */
    public function network_address_aliases_delete($name)
    {
        global $config;
        fauxApiLogger::debug(__METHOD__, array(
            'name' => $name
        ));
        $idx = -1;
        for ($id = 0; $id < count($config["aliases"]["alias"]); $id+=1) {
            if ($config["aliases"]["alias"][$id]['name'] == $name) {
                $idx=$id;
                break;
            }
        }
        if ($idx == -1) {
            //not find
            $error_message = "not find name";
            $error_data = array('name' => $name);
            fauxApiLogger::error($error_message, $error_data);
            throw new \Exception($error_message);
        }
        $usederrmsg = fauxApiInterfaceAliasTools::is_alias_used($name);
        if ($usederrmsg) {
            $error_message = $usederrmsg;
            $error_data = array('name'=>$name);
            fauxApiLogger::error($error_message, $error_data);
            throw new \Exception($error_message);
        }
        \array_splice($config["aliases"]["alias"], $idx, 1);
        if (!fauxApiInterfaceAliasTools::write_config_aliases()) {
            return NULL;
        }

        return $config["aliases"];
    }
}
