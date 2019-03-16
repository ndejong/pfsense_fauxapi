#!/bin/bash

group_name="pfsense"
source_vm="freebsd-11.2-golden"
target_folder="/opt/virtual-hosts-local"

function clone_instance() {

    function usage() {
        echo "Usage: clone_instance -n <node-name> [-a <mac-address1> ] [-b <mac-address2> ] [-p poweron ]" 1>&2;
        exit 1;
    }

    local OPTIND option n a b p

    while getopts ":n:a:b:p:" option; do
        case "${option}" in
            n)
                node_name=${OPTARG}
                ;;
            a)
                macaddress1=${OPTARG:-""}
                ;;
            b)
                macaddress2=${OPTARG:-""}
                ;;
            p)
                poweron=${OPTARG:-""}
                ;;
            *)
                usage
                ;;
        esac
    done
    shift $((OPTIND-1))

    if [ -z "${node_name}" ]; then
        usage
    fi

    # ====

    __running_count=`vboxmanage showvminfo "${node_name}" | grep "^State" | grep "running" | wc -l`

    if [[ ${__running_count} -gt 0 ]]; then
        echo "${node_name} is powered on, skipping"
        return 0
    fi
    echo "${node_name} is being cloned from ${source_vm}"

    if [[ -d "${target_folder}/${group_name}/${node_name}" ]]; then
        vboxmanage unregistervm "${node_name}"
        rm -Rf "${target_folder}/${group_name}/${node_name}"
    fi
    vboxmanage clonevm "${source_vm}" --basefolder "${target_folder}" --register --mode machine --groups "/${group_name}" --name "${node_name}"

    if [[ -z "${macaddress1}" ]]; then
        vboxmanage modifyvm "${node_name}" --nic1 null
        vboxmanage modifyvm "${node_name}" --cableconnected1 off
    else
        vboxmanage modifyvm "${node_name}" --macaddress1 "${macaddress1}"
    fi

    if [[ -z "${macaddress2}" ]]; then
        vboxmanage modifyvm "${node_name}" --nic2 null
        vboxmanage modifyvm "${node_name}" --cableconnected2 off
    else
        vboxmanage modifyvm "${node_name}" --macaddress2 "${macaddress2}"
    fi

    if [[ ${poweron} == "poweron" ]]; then
        vboxmanage startvm "${node_name}" --type gui
    fi

    echo ""
    return 1
}

clone_instance -n "${group_name}-builder1" -a 08002722E841 -p poweron
