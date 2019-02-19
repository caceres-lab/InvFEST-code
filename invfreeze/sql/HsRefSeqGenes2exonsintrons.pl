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

$select = "SELECT idHsRefSeqGenes, exonCount, exonStarts, exonEnds FROM HsRefSeqGenes";
$dbh->{LongReadLen} = 64000;
$dbh->{LongTruncOk} = 1;
$sth = $dbh->prepare($select);
$sth->execute();

while ($genes = $sth->fetchrow_hashref()) {
	
	@exonStarts = split /,/, $$genes{'exonStarts'};
	@exonEnds = split /,/, $$genes{'exonEnds'};
	$counter=1;
	
	while ($counter <= $$genes{'exonCount'}) {
	
		# Insert exon
		$insert = "INSERT INTO HsRefSeqGenes_exons VALUES (".$$genes{'idHsRefSeqGenes'}.", ".$counter.", 
				".$exonStarts[$counter-1].", ".$exonEnds[$counter-1].")";
		$dbh->{LongReadLen} = 64000;
		$dbh->{LongTruncOk} = 1;
		$sth_insert = $dbh->prepare($insert);
		$sth_insert->execute();	
		$sth_insert->finish();
		
		# Insert intron
		if ($counter < $$genes{'exonCount'}) {
		
			$insert = "INSERT INTO HsRefSeqGenes_introns VALUES (".$$genes{'idHsRefSeqGenes'}.", ".$counter.", 
					".($exonEnds[$counter-1]+1).", ".($exonStarts[$counter]-1).")";
			$dbh->{LongReadLen} = 64000;
			$dbh->{LongTruncOk} = 1;
			$sth_insert = $dbh->prepare($insert);
			$sth_insert->execute();	
			$sth_insert->finish();			
		
		}
		
		# Add counter			
		$counter++;
	}
	
}

$sth->finish();
