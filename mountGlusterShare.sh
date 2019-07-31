#checks if gluster mount exists periodicly and if not adds it

#to crontab add
# */5 * * * * /root/mountWebShare.sh >> /logs/mountWebShare.log
#create /logs dir if !exist

#!/bin/bash
#Path variables

NOW=$(date)
string=$(mount | grep localhost)
if [[ $string == *"webShare"* ]]; then
        echo "$NOW - webShare Mounted"
  else
        echo "$NOW - webShare Not Mounted"
        mount -t glusterfs localhost:/webShare /shares/webShare
fi
