#!/bin/bash

#IF File is placed in /etc/network/if-upd/*FILE* make it Executable
#Also to allow openvpn to be called sudo entries need to be made for example
#ALL ALL = NOPASSWD: /bin/systemctl stop openvpn@alexperlap.service
#ALL ALL = NOPASSWD: /bin/systemctl start openvpn@alexperlap.service



#Checks if host is up, host not routable via vpn
Recieved="$(ping -w 5 -c 5 172.16.2.12 | egrep -o [0-9]*[[:space:]]received | grep -o [0-9]*)"

if [ "$Recieved" -gt 0 ];
then
   #host up
   echo stopping vpn
   sudo systemctl stop openvpn@alexperlap.service
else
   #host not up
   echo enabling vpn
   sudo systemctl start openvpn@alexperlap.service
fi

