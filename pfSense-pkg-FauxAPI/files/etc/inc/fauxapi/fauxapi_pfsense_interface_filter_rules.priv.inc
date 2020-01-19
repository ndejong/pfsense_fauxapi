<?php

namespace fauxapi\v1;

// write_config requires functions from this
include_once '/etc/inc/phpsessionmanager.inc';
include_once '/etc/inc/auth.inc';

include_once '/etc/inc/gwlb.inc'; // for  function return_gateway_groups_array;
include_once '/etc/inc/shaper.inc'; // for function get_unique_dnqueue_list

include_once '/etc/inc/util.inc';


class fauxApiFiltersRulesTools
{
    private static function is_specialnet($net)
    {
        $specialsrcdst = explode(" ", "any pppoe l2tp openvpn");

        if (!$net) {
            return false;
        }
        if (in_array($net, $specialsrcdst)) {
            return true;
        } else {
            return false;
        }
    }


    private static function pconfig_to_address(&$adr, $padr, $pmask, $pnot = false, $pbeginport = 0, $pendport = 0)
    {
        $adr = array();

        if ($padr == "any") {
            $adr['any'] = true;
        } else if (fauxApiFiltersRulesTools::is_specialnet($padr)) {
            $adr['network'] = $padr;
        } else {
            $adr['address'] = $padr;
            if (is_ipaddrv6($padr)) {
                if ($pmask != 128) {
                    $adr['address'] .= "/" . $pmask;
                }
            } else {
                if ($pmask != 32) {
                    $adr['address'] .= "/" . $pmask;
                }
            }
        }

        if ($pnot) {
            $adr['not'] = true;
        } else {
            unset($adr['not']);
        }

        if (($pbeginport != 0) && ($pbeginport != "any")) {
            if ($pbeginport != $pendport) {
                $adr['port'] = $pbeginport . "-" . $pendport;
            } else {
                $adr['port'] = $pbeginport;
            }
        }
    }
    private static function do_input_validation($postdata, $reqdfields, $reqdfieldsn, &$input_errors)
    {

        /* check for bad control characters */
        foreach ($postdata as $pn => $pd) {
            if (is_string($pd) && preg_match("/[\\x00-\\x08\\x0b\\x0c\\x0e-\\x1f]/", $pd)) {
                $input_errors[] = sprintf(gettext("The field %s contains invalid characters."), $pn);
            }
        }

        if (is_array($reqdfields)) {
            for ($i = 0; $i < count($reqdfields); $i++) {
                if ($postdata[$reqdfields[$i]] == "") {
                    $input_errors[] = sprintf(gettext("The field %s is required."), $reqdfieldsn[$i]);
                }
            }
        }
    }

    public static function CheckRuleObject($rule)
    {
        global $config;
        global $tcpflags;
        $a_filter = &$config['filter']['rule'];


        $icmplookup = array(
            'inet' => array('name' => 'IPv4', 'icmptypes' => $icmptypes4, 'helpmsg' => gettext('For ICMP rules on IPv4, one or more of these ICMP subtypes may be specified.')),
            'inet6' => array('name' => 'IPv6', 'icmptypes' => $icmptypes6, 'helpmsg' => gettext('For ICMP rules on IPv6, one or more of these ICMP subtypes may be specified.')),
            'inet46' => array('name' => 'IPv4+6', 'icmptypes' => $icmptypes46, 'helpmsg' => sprintf(gettext('For ICMP rules on IPv4+IPv6, one or more of these ICMP subtypes may be specified. (Other ICMP subtypes are only valid under IPv4 %1$sor%2$s IPv6, not both)'), '<i>', '</i>'))
        );
        $a_gatewaygroups = return_gateway_groups_array();
        if (!array_key_exists($rule['ipprotocol'], $icmplookup)) {
            return gettext("The IP protocol is not recognized.");
        }

        // add validation + input error for $rule['interface']
        $valid = ($rule['interface'] == "FloatingRules" || isset($rule['floating'])) ? ['pass', 'block', 'reject', 'match'] : ['pass', 'block', 'reject'];
        if (!(is_string($rule['type'])  && in_array($rule['type'], $valid))) {
            return gettext("A valid rule type is not selected.");
        }

        if (isset($rule['tracker']) && !is_numericint($rule['tracker'])) {
            return "tracker must be integer";
        }

        if (isset($rule['ipprotocol']) && $rule['gateway'] <> '') {
            if (is_array($config['gateways']['gateway_group'])) {
                foreach ($config['gateways']['gateway_group'] as $gw_group) {
                    if ($gw_group['name'] == $rule['gateway'] && $rule['ipprotocol'] != $a_gatewaygroups[$rule['gateway']]['ipprotocol']) {
                        if ($rule['ipprotocol'] == "inet46") {
                            return gettext("Gateways can not be assigned in a rule that applies to both IPv4 and IPv6.");
                        } elseif ($rule['ipprotocol'] == "inet6") {
                            return gettext("An IPv4 gateway group can not be assigned in IPv6 rules.");
                        } elseif ($rule['ipprotocol'] == "inet") {
                            return gettext("An IPv6 gateway group can not be assigned in IPv4 rules.");
                        }
                    }
                }
            }
            if ($iptype = is_ipaddr(lookup_gateway_ip_by_name($rule['gateway']))) {
                // this also implies that  $rule['gateway'] was set and not empty
                if ($rule['ipprotocol'] == "inet46") {
                    return gettext("Gateways can not be assigned in a rule that applies to both IPv4 and IPv6.");
                }
                if (($rule['ipprotocol'] == "inet6") && ($iptype != 6)) {
                    return gettext("An IPv4 gateway can not be assigned in IPv6 rules.");
                }
                if (($rule['ipprotocol'] == "inet") && ($iptype != 4)) {
                    return gettext("An IPv6 gateway can not be assigned in IPv4 rules.");
                }
            }
        }

        if (($rule['proto'] != "tcp") && ($rule['proto'] != "udp") && ($rule['proto'] != "tcp/udp")) {
            $rule['srcbeginport'] = 0;
            $rule['srcendport'] = 0;
            $rule['dstbeginport'] = 0;
            $rule['dstendport'] = 0;
        } else {
            if ($rule['srcbeginport_cust'] && !$rule['srcbeginport']) {
                $rule['srcbeginport'] = trim($rule['srcbeginport_cust']);
            }
            if ($rule['srcendport_cust'] && !$rule['srcendport']) {
                $rule['srcendport'] = trim($rule['srcendport_cust']);
            }
            if ($rule['srcbeginport'] == "any") {
                $rule['srcbeginport'] = 0;
                $rule['srcendport'] = 0;
            } else {
                if (!$rule['srcendport']) {
                    $rule['srcendport'] = $rule['srcbeginport'];
                }
            }
            if ($rule['srcendport'] == "any") {
                $rule['srcendport'] = $rule['srcbeginport'];
            }

            if ($rule['dstbeginport_cust'] && !$rule['dstbeginport']) {
                $rule['dstbeginport'] = trim($rule['dstbeginport_cust']);
            }
            if ($rule['dstendport_cust'] && !$rule['dstendport']) {
                $rule['dstendport'] = trim($rule['dstendport_cust']);
            }

            if ($rule['dstbeginport'] == "any") {
                $rule['dstbeginport'] = 0;
                $rule['dstendport'] = 0;
            } else {
                if (!$rule['dstendport']) {
                    $rule['dstendport'] = $rule['dstbeginport'];
                }
            }
            if ($rule['dstendport'] == "any") {
                $rule['dstendport'] = $rule['dstbeginport'];
            }
        }

        if (fauxApiFiltersRulesTools::is_specialnet($rule['srctype'])) {
            $rule['src'] = $rule['srctype'];
            $rule['srcmask'] = 0;
        } else if ($rule['srctype'] == "single") {
            if (is_ipaddrv6($rule['src'])) {
                $rule['srcmask'] = 128;
            } else {
                $rule['srcmask'] = 32;
            }
        }
        if (fauxApiFiltersRulesTools::is_specialnet($rule['dsttype'])) {
            $rule['dst'] = $rule['dsttype'];
            $rule['dstmask'] = 0;
        } else if ($rule['dsttype'] == "single") {
            if (is_ipaddrv6($rule['dst'])) {
                $rule['dstmask'] = 128;
            } else {
                $rule['dstmask'] = 32;
            }
        }

        $pconfig = $rule;

        if (!isset($pconfig['ipprotocol'])) {
            // other things depend on this, so ensure a valid value if none provided
            $pconfig['ipprotocol'] = "inet";
        }

        if (($rule['proto'] == "icmp") && count($rule['icmptype'])) {
            $pconfig['icmptype'] = implode(',', $rule['icmptype']);
        } else {
            unset($pconfig['icmptype']);
        }

        /* input validation */
        $reqdfields = explode(" ", "type");
        $reqdfieldsn = explode(" ", "type");

        if ($rule['statetype'] == "synproxy state") {
            if ($rule['proto'] != "tcp") {
                return sprintf(gettext("%s is only valid with protocol TCP."), $rule['statetype']);
            }
            if ($rule['gateway'] != "") {
                return sprintf(gettext("%s is only valid if the gateway is set to 'default'."), $rule['statetype']);
            }
        }
        $not_exists_errs = [];
        fauxApiFiltersRulesTools::do_input_validation($rule, $reqdfields, $reqdfieldsn, $not_exists_errs);
        if (count($not_exists_errs) > 0) {
            return join("\n", $not_exists_errs);
        }

        if (!$rule['srcbeginport']) {
            $rule['srcbeginport'] = 0;
            $rule['srcendport'] = 0;
        }
        if (!$rule['dstbeginport']) {
            $rule['dstbeginport'] = 0;
            $rule['dstendport'] = 0;
        }

        if ($rule['srcbeginport'] && !is_port_or_alias($rule['srcbeginport'])) {
            return sprintf(gettext("%s is not a valid start source port. It must be a port alias or integer between 1 and 65535."), $rule['srcbeginport']);
        }
        if ($rule['srcendport'] && !is_port_or_alias($rule['srcendport'])) {
            return sprintf(gettext("%s is not a valid end source port. It must be a port alias or integer between 1 and 65535."), $rule['srcendport']);
        }
        if ($rule['dstbeginport'] && !is_port_or_alias($rule['dstbeginport'])) {
            return sprintf(gettext("%s is not a valid start destination port. It must be a port alias or integer between 1 and 65535."), $rule['dstbeginport']);
        }
        if ($rule['dstendport'] && !is_port_or_alias($rule['dstendport'])) {
            return sprintf(gettext("%s is not a valid end destination port. It must be a port alias or integer between 1 and 65535."), $rule['dstendport']);
        }
        if (!$rule['srcbeginport_cust'] && $rule['srcendport_cust']) {
            if (is_alias($rule['srcendport_cust'])) {
                return 'If a port alias is put in the Source port range to: field the same port alias must be put in the from: field';
            }
        }
        if ($rule['srcbeginport_cust'] && $rule['srcendport_cust']) {
            if (is_alias($rule['srcbeginport_cust']) && is_alias($rule['srcendport_cust']) && $rule['srcbeginport_cust'] != $rule['srcendport_cust']) {
                return 'The same port alias must be used in Source port range from: and to: fields';
            }
            if ((is_alias($rule['srcbeginport_cust']) && (!is_alias($rule['srcendport_cust']) && $rule['srcendport_cust'] != '')) ||
                ((!is_alias($rule['srcbeginport_cust']) && $rule['srcbeginport_cust'] != '') && is_alias($rule['srcendport_cust']))
            ) {
                return 'Numbers and port aliases cannot be specified at the same time in Source port range from: and to: field';
            }
        }
        if (!$rule['dstbeginport_cust'] && $rule['dstendport_cust']) {
            if (is_alias($rule['dstendport_cust'])) {
                return 'If a port alias is put in the Destination port range to: field the same port alias must be put in the from: field';
            }
        }
        if ($rule['dstbeginport_cust'] && $rule['dstendport_cust']) {
            if (is_alias($rule['dstbeginport_cust']) && is_alias($rule['dstendport_cust']) && $rule['dstbeginport_cust'] != $rule['dstendport_cust']) {
                return 'The same port alias must be used in Destination port range from: and to: fields';
            }
            if ((is_alias($rule['dstbeginport_cust']) && (!is_alias($rule['dstendport_cust']) && $rule['dstendport_cust'] != '')) ||
                ((!is_alias($rule['dstbeginport_cust']) && $rule['dstbeginport_cust'] != '') && is_alias($rule['dstendport_cust']))
            ) {
                return 'Numbers and port aliases cannot be specified at the same time in Destination port range from: and to: field';
            }
        }

        if ($rule['src']) {
            $rule['src'] = addrtolower(trim($rule['src']));
        }
        if ($rule['dst']) {
            $rule['dst'] = addrtolower(trim($rule['dst']));
        }

        /* if user enters an alias and selects "network" then disallow. */
        if ($rule['srctype'] == "network") {
            if (is_alias($rule['src'])) {
                return gettext("Alias entries must be a single host or alias.");
            }
        }
        if ($rule['dsttype'] == "network") {
            if (is_alias($rule['dst'])) {
                return gettext("Alias entries must be a single host or alias.");
            }
        }

        if (!fauxApiFiltersRulesTools::is_specialnet($rule['srctype'])) {
            if (($rule['src'] && !is_ipaddroralias($rule['src']))) {
                return sprintf(gettext("%s is not a valid source IP address or alias."), $rule['src']);
            }
            if (($rule['srcmask'] && !is_numericint($rule['srcmask']))) {
                return gettext("A valid source bit count must be specified.");
            }
        }
        if (!fauxApiFiltersRulesTools::is_specialnet($rule['dsttype'])) {
            if (($rule['dst'] && !is_ipaddroralias($rule['dst']))) {
                return sprintf(gettext("%s is not a valid destination IP address or alias."), $rule['dst']);
            }
            if (($rule['dstmask'] && !is_numericint($rule['dstmask']))) {
                return gettext("A valid destination bit count must be specified.");
            }
        }
        if ((is_ipaddr($rule['src']) && is_ipaddr($rule['dst']))) {
            if (!validate_address_family($rule['src'], $rule['dst'])) {
                return gettext("The source and destination IP addresses must have the same family (IPv4 / IPv6).");
            }
        }
        if ((is_ipaddrv6($rule['src']) || is_ipaddrv6($rule['dst'])) && ($rule['ipprotocol'] == "inet")) {
            return gettext("IPv6 addresses cannot be used in IPv4 rules (except within an alias).");
        }
        if ((is_ipaddrv4($rule['src']) || is_ipaddrv4($rule['dst'])) && ($rule['ipprotocol'] == "inet6")) {
            return gettext("IPv4 addresses can not be used in IPv6 rules (except within an alias).");
        }

        if ((is_ipaddr($rule['src']) || is_ipaddr($rule['dst'])) && ($rule['ipprotocol'] == "inet46")) {
            return gettext("IPv4 and IPv6 addresses can not be used in rules that apply to both IPv4 and IPv6 (except within an alias).");
        }

        if ($rule['srcbeginport'] > $rule['srcendport']) {
            /* swap */
            $tmp = $rule['srcendport'];
            $rule['srcendport'] = $rule['srcbeginport'];
            $rule['srcbeginport'] = $tmp;
        }
        if ($rule['dstbeginport'] > $rule['dstendport']) {
            /* swap */
            $tmp = $rule['dstendport'];
            $rule['dstendport'] = $rule['dstbeginport'];
            $rule['dstbeginport'] = $tmp;
        }
        if ($rule['os']) {
            if ($rule['proto'] != "tcp") {
                return gettext("OS detection is only valid with protocol TCP.");
            }
            if (!in_array($rule['os'], $ostypes)) {
                return gettext("Invalid OS detection selection. Please select a valid OS.");
            }
        }

        if ($rule['proto'] == "icmp") {
            $t = $rule['icmptype'];
            if (isset($t) && !is_array($t)) {
                // shouldn't happen but avoids making assumptions for data-sanitising
                return gettext("ICMP types expected to be a list if present, but is not.");
            } elseif (!isset($t) || count($t) == 0) {
                // not specified or none selected
                unset($rule['icmptype']);
            } elseif (isset($rule['ipprotocol'])) {
                // check data; if ipprotocol invalid then safe to skip this (we can't determine valid icmptypes, but input error already raised for ipprotocol)
                $bad_types = array();
                if ((count($t) == 1 && !isset($t['any'])) || count($t) > 1) {
                    // Only need to check valid if just one selected != "any", or >1 selected
                    $p = $rule['ipprotocol'];
                    foreach ($t as $type) {
                        if (($p == 'inet' && !array_key_exists($type, $icmptypes4)) ||
                            ($p == 'inet6' && !array_key_exists($type, $icmptypes6)) ||
                            ($p == 'inet46' && !array_key_exists($type, $icmptypes46))
                        ) {
                            $bad_types[] = $type;
                        }
                    }
                }
                if (count($bad_types) > 0) {
                    return sprintf(gettext("Invalid ICMP subtype: %s can not be used with %s."), implode(';', $bad_types),  $t['name']);
                }
            }
        } else {
            unset($rule['icmptype']); // field not applicable, might hold junk from old hidden selections. Unset it.
        }

        if ($rule['ackqueue'] != "") {
            if ($rule['defaultqueue'] == "") {
                return gettext("A queue must be selected when an acknowledge queue is also selected.");
            } else if ($rule['ackqueue'] == $rule['defaultqueue']) {
                return gettext("Acknowledge queue and Queue cannot be the same.");
            }
        }
        if (isset($rule['floating']) && $rule['pdnpipe'] != "" && (empty($rule['direction']) || $rule['direction'] == "any")) {
            return gettext("Limiters can not be used in Floating rules without choosing a direction.");
        }
        if (isset($rule['floating']) && $rule['gateway'] != "" && (empty($rule['direction']) || $rule['direction'] == "any")) {
            return gettext("Gateways can not be used in Floating rules without choosing a direction.");
        }

        ///////////////////////////////////////////////////////////////////////////
        // check for sharper
        $dnqlist = &get_unique_dnqueue_list();
        if ($rule['pdnpipe'] && $rule['pdnpipe'] != "") {
            if ($rule['dnpipe'] == "") {
                return gettext("A queue must be selected for the In direction before selecting one for Out too.");
            } else if ($rule['pdnpipe'] == $rule['dnpipe']) {
                return gettext("In and Out Queue cannot be the same.");
            } else if ($dnqlist[$rule['pdnpipe']][0] == "?" && $dnqlist[$rule['dnpipe']][0] <> "?") {
                return gettext("A queue and a virtual interface cannot be selected for IN and Out. Both must be from the same type.");
            } else if ($dnqlist[$rule['dnpipe']][0] == "?" && $dnqlist[$rule['pdnpipe']][0] <> "?") {
                return gettext("A queue and a virtual interface cannot be selected for IN and Out. Both must be from the same type.");
            }
            if ($rule['direction'] == "out" && empty($rule['gateway'])) {
                return gettext("Please select a gateway, normally the interface selected gateway, so the limiters work correctly");
            }
        }
        if (!empty($rule['ruleid']) && !is_numericint($rule['ruleid'])) {
            return gettext('ID must be an integer');
        }

        if (!in_array($rule['proto'], array("tcp", "tcp/udp"))) {
            if (!empty($rule['max-src-conn'])) {
                return gettext("The maximum number of established connections per host (advanced option) can only be specified for TCP protocol.");
            }
            if (!empty($rule['max-src-conn-rate']) || !empty($rule['max-src-conn-rates'])) {
                return gettext("The maximum new connections per host / per second(s) (advanced option) can only be specified for TCP protocol.");
            }
            if (!empty($rule['statetimeout'])) {
                return gettext("The state timeout (advanced option) can only be specified for TCP protocol.");
            }
        }

        if ($rule['type'] <> "pass") {
            if (!empty($rule['max'])) {
                return gettext("The maximum state entries (advanced option) can only be specified for Pass type rules.");
            }
            if (!empty($rule['max-src-nodes'])) {
                return gettext("The maximum number of unique source hosts (advanced option) can only be specified for Pass type rules.");
            }
            if (!empty($rule['max-src-conn'])) {
                return gettext("The maximum number of established connections per host (advanced option) can only be specified for Pass type rules.");
            }
            if (!empty($rule['max-src-states'])) {
                return gettext("The maximum state entries per host (advanced option) can only be specified for Pass type rules.");
            }
            if (!empty($rule['max-src-conn-rate']) || !empty($rule['max-src-conn-rates'])) {
                return gettext("The maximum new connections per host / per second(s) (advanced option) can only be specified for Pass type rules.");
            }
            if (!empty($rule['statetimeout'])) {
                return gettext("The state timeout (advanced option) can only be specified for Pass type rules.");
            }
        }

        if ($rule['statetype'] == "none") {
            if (!empty($rule['max'])) {
                return gettext("The maximum state entries (advanced option) cannot be specified if statetype is none.");
            }
            if (!empty($rule['max-src-nodes'])) {
                return gettext("The maximum number of unique source hosts (advanced option) cannot be specified if statetype is none.");
            }
            if (!empty($rule['max-src-conn'])) {
                return gettext("The maximum number of established connections per host (advanced option) cannot be specified if statetype is none.");
            }
            if (!empty($rule['max-src-states'])) {
                return gettext("The maximum state entries per host (advanced option) cannot be specified if statetype is none.");
            }
            if (!empty($rule['max-src-conn-rate']) || !empty($rule['max-src-conn-rates'])) {
                return gettext("The maximum new connections per host / per second(s) (advanced option) cannot be specified if statetype is none.");
            }
            if (!empty($rule['statetimeout'])) {
                return gettext("The state timeout (advanced option) cannot be specified if statetype is none.");
            }
        }

        if (($rule['max'] != "") && !is_posnumericint($rule['max'])) {
            return gettext("Maximum state entries (advanced option) must be a positive integer");
        }

        if (($rule['max-src-nodes'] != "") && !is_posnumericint($rule['max-src-nodes'])) {
            return gettext("Maximum number of unique source hosts (advanced option) must be a positive integer");
        }

        if (($rule['max-src-conn'] != "") && !is_posnumericint($rule['max-src-conn'])) {
            return gettext("Maximum number of established connections per host (advanced option) must be a positive integer");
        }

        if (($rule['max-src-states'] != "") && !is_posnumericint($rule['max-src-states'])) {
            return gettext("Maximum state entries per host (advanced option) must be a positive integer");
        }

        if (($rule['max-src-conn-rate'] != "") && !is_posnumericint($rule['max-src-conn-rate'])) {
            return gettext("Maximum new connections per host / per second(s) (advanced option) must be a positive integer");
        }

        if (($rule['statetimeout'] != "") && !is_posnumericint($rule['statetimeout'])) {
            return gettext("State timeout (advanced option) must be a positive integer");
        }

        if ((($rule['max-src-conn-rate'] <> "" and $rule['max-src-conn-rates'] == "")) ||
            (($rule['max-src-conn-rate'] == "" and $rule['max-src-conn-rates'] <> ""))
        ) {
            return gettext("Both maximum new connections per host and the interval (per second(s)) must be specified");
        }

        if (!$rule['tcpflags_any']) {
            $settcpflags = array();
            $outoftcpflags = array();
            foreach ($tcpflags as $tcpflag) {
                if ($rule['tcpflags1_' . $tcpflag] == "on") {
                    $settcpflags[] = $tcpflag;
                }
                if ($rule['tcpflags2_' . $tcpflag] == "on") {
                    $outoftcpflags[] = $tcpflag;
                }
            }
            if (empty($outoftcpflags) && !empty($settcpflags)) {
                return gettext("If TCP flags that should be set is specified, then out of which flags should be specified as well.");
            }
        }

        if ($rule['dscp'] && !in_array($rule['dscp'], $firewall_rules_dscp_types)) {
            return gettext("Invalid DSCP value.");
        }
        if ($rule['tag'] && !is_validaliasname($rule['tag'])) {
            return gettext("Invalid tag value.");
        }
        if ($rule['tagged'] && !is_validaliasname($rule['tagged'])) {
            return gettext("Invalid tagged value.");
        }
        if ($rule['statetype'] && !array_key_exists($rule['statetype'], $statetype_values)) {
            return gettext("Invalid State Type.");
        }
        if ($rule['vlanprio'] && !array_key_exists($rule['vlanprio'], $vlanprio)) {
            return gettext("Invalid VLAN Prio.");
        }
        if ($rule['vlanprioset'] && !array_key_exists($rule['vlanprioset'], $vlanprio)) {
            return gettext("Invalid VLAN Prio Set.");
        }

        if ($rule['ackqueue'] && !array_key_exists($rule['ackqueue'], $list)) {
            return gettext("Invalid ACK Queue.");
        }
        if ($rule['defaultqueue'] && !array_key_exists($rule['defaultqueue'], $list)) {
            return gettext("Invalid Default Queue.");
        }

        if ($rule['dnpipe'] && !array_key_exists($rule['dnpipe'], $dnqlist)) {
            return gettext("Invalid In Pipe.");
        }
        if ($rule['pdnpipe'] && !array_key_exists($rule['pdnpipe'], $dnqlist)) {
            return gettext("Invalid Out Pipe.");
        }
        return NULL;
    }

    public static function BuildRuleConfig($rule)
    {
        global $tcpflags;
        global $config;
        $filterent = array();
        $filterent['id'] = $rule['ruleid'] > 0 ? $rule['ruleid'] : '';

        $filterent['tracker'] = empty($rule['tracker']) ? (int) microtime(true) : $rule['tracker'];

        $filterent['type'] = $rule['type'];

        if (isset($rule['interface'])) {
            $filterent['interface'] = $rule['interface'];
        } // FIXME: can $rule['interface'] be unset at this point, if so then what?

        $filterent['ipprotocol'] = $rule['ipprotocol'];

        if ($rule['tcpflags_any']) {
            $filterent['tcpflags_any'] = true;
        } else {
            $settcpflags = array();
            $outoftcpflags = array();
            foreach ($tcpflags as $tcpflag) {
                if ($rule['tcpflags1_' . $tcpflag] == "on") {
                    $settcpflags[] = $tcpflag;
                }
                if ($rule['tcpflags2_' . $tcpflag] == "on") {
                    $outoftcpflags[] = $tcpflag;
                }
            }
            if (!empty($outoftcpflags)) {
                $filterent['tcpflags2'] = join(",", $outoftcpflags);
                if (!empty($settcpflags)) {
                    $filterent['tcpflags1'] = join(",", $settcpflags);
                }
            }
        }

        if (isset($rule['tag'])) {
            $filterent['tag'] = $rule['tag'];
        }
        if (isset($rule['tagged'])) {
            $filterent['tagged'] = $rule['tagged'];
        }
        $if = $rule['interface'];
        if ($if == "FloatingRules" || isset($rule['floating'])) {
            $filterent['direction'] = $rule['direction'];
            if (isset($rule['quick']) && $rule['quick'] <> "") {
                $filterent['quick'] = $rule['quick'];
            }
            $filterent['floating'] = "yes";
            if (isset($rule['interface']) && count($rule['interface']) > 0) {
                $filterent['interface'] = implode(",", $rule['interface']);
            }
        }

        /* Advanced options */
        if ($rule['allowopts'] == "yes") {
            $filterent['allowopts'] = true;
        } else {
            unset($filterent['allowopts']);
        }
        if ($rule['disablereplyto'] == "yes") {
            $filterent['disablereplyto'] = true;
        } else {
            unset($filterent['disablereplyto']);
        }
        $filterent['max'] = $rule['max'];
        $filterent['max-src-nodes'] = $rule['max-src-nodes'];
        $filterent['max-src-conn'] = $rule['max-src-conn'];
        $filterent['max-src-states'] = $rule['max-src-states'];
        $filterent['statetimeout'] = $rule['statetimeout'];
        $filterent['statetype'] = $rule['statetype'];
        $filterent['os'] = $rule['os'];
        if ($rule['nopfsync'] <> "") {
            $filterent['nopfsync'] = true;
        } else {
            unset($filterent['nopfsync']);
        }

        /* Nosync directive - do not xmlrpc sync this item */
        if ($rule['nosync'] <> "") {
            $filterent['nosync'] = true;
        } else {
            unset($filterent['nosync']);
        }

        /* unless both values are provided, unset the values - ticket #650 */
        if ($rule['max-src-conn-rate'] <> "" and $rule['max-src-conn-rates'] <> "") {
            $filterent['max-src-conn-rate'] = $rule['max-src-conn-rate'];
            $filterent['max-src-conn-rates'] = $rule['max-src-conn-rates'];
        } else {
            unset($filterent['max-src-conn-rate']);
            unset($filterent['max-src-conn-rates']);
        }

        if ($rule['proto'] != "any") {
            $filterent['protocol'] = $rule['proto'];
        } else {
            unset($filterent['protocol']);
        }

        // Convert array of selected ICMP types to comma-separated string, for backwards compatibility (previously only allowed one type per rule)
        if ($rule['proto'] == "icmp" && is_array($rule['icmptype']) && !isset($rule['icmptype']['any']) && count($rule['icmptype']) > 0) {
            //if any of these conditions not met, rule would apply to all icmptypes, so we would unset
            $filterent['icmptype'] = implode(',', $rule['icmptype']);
        } else {
            unset($filterent['icmptype']);
        }

        fauxApiFiltersRulesTools::pconfig_to_address(
            $filterent['source'],
            $rule['src'],
            $rule['srcmask'],
            $rule['srcnot'],
            $rule['srcbeginport'],
            $rule['srcendport']
        );

        fauxApiFiltersRulesTools::pconfig_to_address(
            $filterent['destination'],
            $rule['dst'],
            $rule['dstmask'],
            $rule['dstnot'],
            $rule['dstbeginport'],
            $rule['dstendport']
        );

        if ($rule['disabled']) {
            $filterent['disabled'] = true;
        } else {
            unset($filterent['disabled']);
        }

        if ($rule['dscp']) {
            $filterent['dscp'] = $rule['dscp'];
        }

        if ($rule['log']) {
            $filterent['log'] = true;
        } else {
            unset($filterent['log']);
        }

        $filterent['descr'] = trim($rule['descr']);

        if ($rule['gateway'] != "") {
            $filterent['gateway'] = $rule['gateway'];
        }

        if ($rule['defaultqueue'] != "") {
            $filterent['defaultqueue'] = $rule['defaultqueue'];
            if ($rule['ackqueue'] != "") {
                $filterent['ackqueue'] = $rule['ackqueue'];
            }
        }

        if ($rule['dnpipe'] != "") {
            $filterent['dnpipe'] = $rule['dnpipe'];
            if ($rule['pdnpipe'] != "") {
                $filterent['pdnpipe'] = $rule['pdnpipe'];
            }
        }

        if ($rule['sched'] != "") {
            $filterent['sched'] = $rule['sched'];
        }

        if ($rule['vlanprio'] != "") {
            $filterent['vlanprio'] = $rule['vlanprio'];
        }
        if ($rule['vlanprioset'] != "") {
            $filterent['vlanprioset'] = $rule['vlanprioset'];
        }


        $a_filter = &$config['filter']['rule'];
        // for modify
        $id = $rule["id"];
        // If we have an associated nat rule, make sure the source and destination doesn't change
        if (isset($a_filter[$id]['associated-rule-id'])) {
            $filterent['interface'] = $a_filter[$id]['interface'];
            if (isset($a_filter[$id]['protocol'])) {
                $filterent['protocol'] = $a_filter[$id]['protocol'];
            } else if (isset($filterent['protocol'])) {
                unset($filterent['protocol']);
            }
            if ($a_filter[$id]['protocol'] == "icmp" && $a_filter[$id]['icmptype']) {
                $filterent['icmptype'] = $a_filter[$id]['icmptype'];
            } else if (isset($filterent['icmptype'])) {
                unset($filterent['icmptype']);
            }

            $filterent['source'] = $a_filter[$id]['source'];
            $filterent['destination'] = $a_filter[$id]['destination'];
            $filterent['associated-rule-id'] = $a_filter[$id]['associated-rule-id'];
        }

        if (isset($a_filter[$id]['created']) && is_array($a_filter[$id]['created'])) {
            $filterent['created'] = $a_filter[$id]['created'];
        } else {
            $filterent['created'] = make_config_revision_entry();
        }

        $filterent['updated'] = make_config_revision_entry();
        return $filterent;
    }

    public static function WriteConfig()
    {
        if (write_config(gettext("Firewall: Rules - saved/edited a firewall rule."))) {
            mark_subsystem_dirty('filter');
        }
    }
}
