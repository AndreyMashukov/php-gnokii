#! /bin/sh
# mock for gnokii answer

echo GNOKII Version 0.6.31

if [ "$3" = "--sendsms" ]
then
echo Send succeeded with reference -1!
elif [ "$3" = "--getsms" ]
then
    if [ "$5" = "0" ]
    then
	echo 0. Inbox Message \(Read\)
	echo Date/time: 02/09/2017 08:40:07 +0300
	echo Sender: Provider Msg Center: +79043490004
	echo Text:
	echo You balance is 200\$
	exit 0
    fi
    if [ "$5" = "8" ]
    then
	echo 8. Inbox Message \(Unread\)
	echo Date/time: 04/09/2017 14:17:29 +0700
	echo Sender: +79526191914 Msg Center: +79139869993
	echo Linked:
	echo Linked \(1/2\):
	echo Message part in first message an
	exit 0
    fi
    if [ "$5" = "9" ]
    then
	echo 9. Inbox Message \(Unread\)
	echo Date/time: 04/09/2017 14:17:31 +0700
	echo Sender: +79526191914 Msg Center: +79139869993
	echo Linked:
	echo Linked \(2/2\):
	echo d second part of message
	exit 0
    else
	echo Getting SMS failed \(location $5 from $4 memory\)! \(The given location is empty.\)
	exit 1
    fi
elif [ "$3" = "--deletesms" ]
then
    if [ "$5" != "2" ] && [ "$6" != "2" ]
    then
	i=$5
	step=1
	while [ "$i" -lt "$6" ] || [ "$i" == "$6" ]
	do
	    echo Deleted SMS \(location "$i" from memory $4\)
	    i=$((i+step))
	done
	exit 0
    fi
fi

exit 0