#!/bin/bash


#Gets Connected SSID
SSID="$(iwconfig wlp3s0 | grep ESSID | cut -d ":" -f2 | sed 's/^[^"]*"\|"[^"]*$//g')"
echo Connected SSID:$SSID

#SSID to toogle on
Wanted_SSID="Goats"
echo Wanted_SSID:$Wanted_SSID


if [ "$Wanted_SSID" == "$SSID" ]; 
then
   #Connected to Wanted_SSID
   echo Connected to Home network disabling vpn
   sudo systemctl stop openvpn@alexperlap.service
else
   #Not Conected to Wanted_SSID
   echo Not connected to home network, enabling vpn
   sudo systemctl start openvpn@alexperlap.service
fi
