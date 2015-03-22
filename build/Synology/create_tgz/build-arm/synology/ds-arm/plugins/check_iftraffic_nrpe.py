#!/usr/bin/python
#
# NRPE plugin to monitor network traffic
#
# Script based on check_iftraffic_nrpe.pl by Van Dyck Sven
#
# Website: https://github.com/samyboy/check_iftraffic_nrpe
#

import sys
import time
import argparse

__version__ = '0.2'
__author__ = 'Samuel Krieg'


def bits2bytes(bits):
    return bits / 8


def load_traffic(data_file):
    """load the traffic data from a file."""
    traffic = dict()
    last_time = 0.0
    try:
        f = open(data_file)
    except IOError:
        return 0.0, traffic

    i = 0
    for line in f:
        i += 1
        if i == 1:
            last_time = float(line.strip())
        else:
            data = line.split()
            traffic[data[0]] = {'rxbytes': int(data[1]),
                                'txbytes': int(data[2])}
    return last_time, traffic


def save_traffic(data, data_file):

    f = open(data_file, 'w')
    f.write(str(time.time()) + "\n")
    for if_name, if_data in data.iteritems():
        f.write("%s\t%s\t%s\n" %
                (if_name, if_data['rxbytes'], if_data['txbytes']))


def get_traffic():
    """list all the network data"""
    traffic = dict()
    my_file = open('/proc/net/dev')
    i = 0
    for line in my_file:
        i += 1
        if i > 2:  # skip the 2 first lines
            data = dict()
            iface_name, iface_data = line.split(':')
            iface_name = iface_name.strip()
            data_values = iface_data.split()
            # receive:
            # 0
            # bytes    packets errs drop fifo frame compressed multicast
            # transmit:
            # 8
            # bytes    packets errs drop fifo colls carrier compressed
            data['rxbytes'] = int(data_values[0])
            data['txbytes'] = int(data_values[8])
            traffic[iface_name] = data
    return traffic


def parse_arguments():

    global __author__
    global __version__

    version_string = "%(prog)s-%(version)s by %(author)s" % \
                     {"prog": "%(prog)s", "version": __version__, \
                     "author": __author__}

    p = argparse.ArgumentParser(description="Description",
        formatter_class=argparse.RawDescriptionHelpFormatter)
    g = p.add_mutually_exclusive_group()

    p.add_argument('-V', '--version', action='version',
                   help="shows program version", version=version_string)
    p.add_argument('-c', '--critical', type=int, default=98,
                   help='Warning')
    p.add_argument('-w', '--warning', type=int, default=85,
                   help='Warning')
    p.add_argument('-b', '--bandwidth', type=int, default=131072000,
                   help='Bandwidth in bytes/s \
                        (default 131072000 = 1000Mb/s * 1024 * 1024 / 8. \
                        Yes, you must calculate.')
    g.add_argument('-i', '--interfaces', nargs='*',
                   help='interface (default: all interfaces)')
    g.add_argument('-x', '--exclude', nargs='*',
                   help='if all interfaces, then exclude some')
    #p.add_argument('-u', '--units', type=str, choices=['G', 'M', 'k'],
    #               help='units')
    #p.add_argument('-B', '--total', action=store_true,
    #               help='calculate total of interfaces')

    return p.parse_args()


def max_counter():
    if sys.maxsize > 2 ** 32:
        return 2 ** 64 - 1
    else:
        return 2 ** 32 - 1


def calc_diff(value1, value2):
    if value1 > value2:
        return max_counter() - value1 + value2
    else:
        """ normal behaviour """
        return value2 - value1


def get_traffic_status(xbytes, bandwidth, crit, warn):
    if xbytes > crit * (bandwidth / 100):
        return 'CRITICAL'
    if xbytes > warn * (bandwidth / 100):
        return 'WARNING'
    return 'OK'


def worst_status(status1, status2):
    global _status_codes
    status_order = ['CRITICAL', 'WARNING', 'UNKNOWN', 'OK']
    for status in status_order:
        if status1 == status or status2 == status:
            return status


def get_perfdata(label, value, warn_level, crit_level, min_level, max_level):
    return ("%(label)s=%(value).2f;" % {'label': label, 'value': value} + \
            '%(warn_level)d;%(crit_level)d;%(min_level)d;%(max_level)d' % \
            {'warn_level': warn_level, 'crit_level': crit_level,
             'min_level': min_level, 'max_level': max_level})


def main():

    args = parse_arguments()
    _status_codes = {'OK': 0, 'WARNING': 1, 'CRITICAL': 2, 'UNKNOWN': 3}
    exit_status = 'OK'
    data_file = '/var/tmp/traffic_stats.dat'
    bandwidth = args.bandwidth
    problems = []

    # capture all the data from the system
    traffic_data = get_traffic()

    # load the previous data
    previous_traffic_time, if_data0 = load_traffic(data_file)

    # save the data from the system
    try:
        save_traffic(traffic_data, data_file)
    except IOError:
        problems.append("Cannot write in %s" % data_file)
        exit_status = 'UNKNOWN'

    # get the time between the two metrics
    elapsed_time = time.time() - previous_traffic_time

    # remove interfaces if needed
    if args.exclude:
        for x in args.exclude:
            if x in traffic_data:
                del traffic_data[x]

    if args.interfaces:
        traffic_data2 = dict()
        for i in args.interfaces:
            if i in traffic_data:
                traffic_data2[i] = traffic_data[i]
            else:
                """The User wants a non existent interface..."""
                problems.append("Interface %s not found" % i)
                exit_status = 'CRITICAL'
        traffic_data = traffic_data2

    # calculate the results and the output
    perfdata = []

    if not if_data0:
        if not problems:
            problems.append("First run.")
    else:
        for if_name, if_data1 in traffic_data.iteritems():
            # calculate the bytes
            txbytes = calc_diff(if_data0[if_name]['txbytes'],
                                if_data1['txbytes'])
            rxbytes = calc_diff(if_data0[if_name]['rxbytes'],
                                if_data1['rxbytes'])
            # calculate the bytes per second
            txbytes = txbytes / elapsed_time
            rxbytes = rxbytes / elapsed_time
            # determine a status for TX
            new_exit_status = get_traffic_status(txbytes, bandwidth,
                                                 args.critical, args.warning)
            if new_exit_status != 'OK':
                problems.append("%s: %sMbs/%sMbs" % \
                                (if_name, txbytes, bandwidth))
            exit_status = worst_status(exit_status, new_exit_status)
            # determine a status for RX
            new_exit_status = get_traffic_status(rxbytes, bandwidth,
                                                 args.critical, args.warning)
            if new_exit_status != 'OK':
                problems.append("%s: %sMbs/%sMbs" % \
                                (if_name, rxbytes, bandwidth))
            exit_status = worst_status(exit_status, new_exit_status)

            """ get perfdata values
            perfdata format (in 1 line):
            (user_readable_message_for_nagios) | (label)=(value)(metric);
            (warn level);(crit level);(min level);(max level)
            """

            warn_level = args.warning * (bandwidth / 100)
            crit_level = args.critical * (bandwidth / 100)
            min_level = 0.0
            max_level = bandwidth

            perfdata.append(get_perfdata('out-' + if_name, txbytes, warn_level,
            crit_level, min_level, max_level))
            perfdata.append(get_perfdata('in-' + if_name, rxbytes, warn_level,
            crit_level, min_level, max_level))

    print "TRAFFIC %s: %s | %s " % (exit_status, '_'.join(problems),
                                   ' '.join(perfdata))

    sys.exit(_status_codes[exit_status])

if __name__ == '__main__':
    main()
