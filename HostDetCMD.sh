#!/bin/bash

#Checks if host is up, host not routable via vpn
Recieved="$(ping -w 5 -c 5 172.16.2.12 | grep received | cut -c 24)"

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

