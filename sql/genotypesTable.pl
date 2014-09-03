#!/usr/bin/perl

# Connect database
use DBI;
use DBD::mysql;

my $host = "localhost";
my $user = "invfestdb-user";
my $pass = "invfestdb-user";
my $database = "INVFEST-DB-PUBLIC";

my $dsn = "DBI:mysql:host=$host;port=3306";    # from other computers (mendel, ...)
my $emsg= "Could not access the Database\n Could not open DSN $dsn"; 
my $dbh = DBI->connect($dsn, $user, $pass, {RaiseError => 1, 
	 PrintError => 1, 
	 AutoCommit => 1, 
	 LongReadLen => 4000}) || warn("$emsg: " . $DBI::errstr .__LINE__."\n");

$dbh->do("USE `$database`");

# Output file
open(OUTFILE, '>genotypesTable.csv');
print OUTFILE "individual,population,gender,family,relationship,panel,";

# Get inversion names
$select_names = "SELECT DISTINCT mt.`name` FROM individuals_detection id, inversions mt WHERE id.inversions_id=mt.id AND id.genotype IS NOT NULL ORDER BY mt.`name`";
$dbh->{LongReadLen} = 64000;
$dbh->{LongTruncOk} = 1;
$sth = $dbh->prepare($select_names);
$sth->execute();

@inv_names=(); @substr_bigtable=();
while($invinfo=$sth->fetchrow_hashref()) {

	push(@substr_bigtable, "MAX(IF(raw.name='".$invinfo->{name}."' AND raw.genotype2 IS NOT NULL,raw.genotype2,'')) AS '".$invinfo->{name}."'");
	push(@inv_names, $invinfo->{name});
	print OUTFILE $invinfo->{name}.",";

}
$sth->finish();
print OUTFILE "\n";

# Build sql query
$select_bigtable = "SELECT raw.code, raw.nickname, raw.gender, raw.population, raw.family, raw.relationship, raw.panel, ".join(', ', @substr_bigtable)."

FROM (
    SELECT i.code, i.nickname, i.gender, i.population, i.family, i.relationship, i.panel, mt.name, IF(id.genotype = 'INV/STD', 'STD/INV', id.genotype) AS genotype2
    FROM individuals_detection id, individuals i, inversions mt
    WHERE i.id=id.individuals_id AND id.inversions_id=mt.id AND id.genotype IS NOT NULL 
) raw

GROUP BY raw.code";

$dbh->{LongReadLen} = 64000;
$dbh->{LongTruncOk} = 1;
$sth = $dbh->prepare($select_bigtable);
$sth->execute();

@substr_bigtable=();
while($invinfo=$sth->fetchrow_hashref()) {

	print OUTFILE $invinfo->{code}.",".$invinfo->{population}.",".$invinfo->{gender}.",".$invinfo->{family}.",".$invinfo->{relationship}.",".$invinfo->{panel}.",";
	foreach $name (@inv_names) {
		print OUTFILE $invinfo->{$name}.",";
	}
	print OUTFILE "\n";

}
$sth->finish();

close OUTFILE;
