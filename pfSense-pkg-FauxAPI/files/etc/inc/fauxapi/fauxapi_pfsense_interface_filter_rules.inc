<?php

namespace fauxapi\v1;

include 'fauxapi_pfsense_interface_filter_rules.priv.inc';
trait network_filter_rules
{
    /**
     * filter_rules_get()
     * 
     * @return array
     */
    public function filter_rules_get()
    {
        global $config;
        fauxApiLogger::debug(__METHOD__);
        return $config["filter"]["rule"];
    }

    /**
     * filter_rules_create()
     * 
     * @return array
     */
    public function filter_rules_create($position, $ruleobj)
    {
        global $config;
        fauxApiLogger::debug(__METHOD__, array("rule" => $ruleobj));
        if (isset($ruleobj["id"])) {
            $error_message = "rules create obj could not have id";
            $error_data = array('ruleobj' => $ruleobj);
            fauxApiLogger::error($error_message, $error_data);
            throw new \Exception($error_message);
        }
        $error_message = fauxApiFiltersRulesTools::CheckRuleObject($ruleobj);
        if ($error_message != NULL) {
            $error_data = array('ruleobj' => $ruleobj);
            fauxApiLogger::error($error_message, $error_data);
            throw new \Exception($error_message);
        }
        $target = fauxApiFiltersRulesTools::BuildRuleConfig($ruleobj);
        // insert position
        array_splice($config["filter"]["rule"], $position, 0, array($target));
        filter_rules_sort();
        fauxApiFiltersRulesTools::WriteConfig();
        return $config["filter"]["rule"];
    }


    /**
     * filter_rules_delete_by_position()
     * 
     * @return array
     */
    public function filter_rules_delete_by_position($position)
    {
        global $config;
        \array_splice($config["filter"]["rule"], $position, 1);
        filter_rules_sort();
        fauxApiFiltersRulesTools::WriteConfig();
        return $config["filter"]["rule"];
    }
}
