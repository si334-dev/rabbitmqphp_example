#!bin/bash

(crontab -l 2>/dev/null; echo "0 8 * * 1 ./doWeekEval.php") | crontab -

