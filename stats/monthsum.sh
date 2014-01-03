#get month year
y=$(date +'%Y')
m=$(date +'%m')
DATE=$y.$m
#get num hits per day
sort -u hits.txt | cut -d " " -f 2,3,4 | uniq -c > $DATE.xday.txt
#do bzip hits
bzip2 -zkv --best hits.txt
mv hits.txt.bz2 $DATE.raw.bz2
#do bzip2 summary
bzip2 -zkv --best $DATE.xday.txt



