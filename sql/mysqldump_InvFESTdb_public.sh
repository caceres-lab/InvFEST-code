#!/bin/sh

# Dump INVFEST-DB into a temporal file
echo "------Dumping INVFEST-DB into a temporal file "
DBPRIVATE=INVFEST-DB
DBPUBLIC=INVFEST-DB-PUBLIC
DBUSER=invfest
DBPASSWORD=pwdInvFEST

TBLIST=`mysql -u ${DBUSER} -p${DBPASSWORD} -AN -e"SELECT GROUP_CONCAT(table_name SEPARATOR ' ') FROM information_schema.tables WHERE table_schema='${DBPRIVATE}' AND table_name NOT IN ('user','log_task','rmsk') AND table_name NOT LIKE '%_old';"`

mysqldump -u ${DBUSER} -p${DBPASSWORD} ${DBPRIVATE} ${TBLIST} > InvFESTdb_tmp.sql

# Restore INVFEST-DB-PUBLIC from the temporal file
echo "------Restoring INVFEST-DB-PUBLIC from the temporal file "
mysql -u ${DBUSER} -p${DBPASSWORD} ${DBPUBLIC} < InvFESTdb_tmp.sql

# Delete information from a list of RESEARCHS
echo "------Deleting information from a list of RESEARCHS "

qry='DELETE FROM researchs WHERE researchs.`name` IN ("Puig et al. 2013a","Puig et al. 2013b","Vicente et al. 2013","Villatoro et al. 2013a","Villatoro et al. 2013b");'
echo $qry

mysql -u ${DBUSER} -p${DBPASSWORD} ${DBPUBLIC} << eof
$qry
eof

# Update breakpoints (through a stored procedure)
echo "------Updating breakpoints (through a stored procedure)"

qry='CALL inv_iterator();'
#qry="CALL update_BP_public();"
echo $qry

mysql -u ${DBUSER} -p${DBPASSWORD} ${DBPUBLIC} << eof
$qry
eof

# Update summary data in `inversions` table (through a stored procedure)
echo "------Updating summary data in inversions table (through a stored procedure) "

qry='CALL update_summaryData_public();'
echo $qry

mysql -u ${DBUSER} -p${DBPASSWORD} ${DBPUBLIC} << eof
$qry
eof

# Dump INVFEST-DB-PUBLIC into a file
echo "------Dumping INVFEST-DB-PUBLIC into a file "
mysqldump -u ${DBUSER} -p${DBPASSWORD} ${DBPUBLIC} > InvFESTdb.sql
gzip -q -c InvFESTdb.sql > InvFESTdb.sql.gz

# Save INVFEST-DB as backup
gzip -q -c InvFESTdb_tmp.sql > InvFESTdb.private.backup.sql.gz

# Remove temporal files
echo "------Removing temporal files "
rm InvFESTdb_tmp.sql
rm InvFESTdb.sql

# Create UCSC custom tracks
echo "------Generating UCSC tracks "
perl generateUCSCtracks.pl
echo "======PLEASE REMEMBER TO UPDATE TRACKS IN THE UCSC InvFEST SESSION!!! "

# Create genotypes table
echo "------Generating genotypes table "
perl genotypesTable.pl

# End of script
echo "------End of script "
