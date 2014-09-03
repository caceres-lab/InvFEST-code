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

# End of chromosomes
%EndOfChromosomes = (	'chr1' => 247249719,
			'chr1_random' => 1663265,
			'chr2' => 242951149,
			'chr2_random' => 185571,
			'chr3' => 199501827,
			'chr3_random' => 749256,
			'chr4' => 191273063,
			'chr4_random' => 842648,
			'chr5' => 180857866,
			'chr5_random' => 143687,
			'chr6' => 170899992,
			'chr6_random' => 1875562,
			'chr7' => 158821424,
			'chr7_random' => 549659,
			'chr8' => 146274826,
			'chr8_random' => 943810,
			'chr9' => 140273252,
			'chr9_random' => 1146434,
			'chr10' => 135374737,
			'chr10_random' => 113275,
			'chr11' => 134452384,
			'chr11_random' => 215294,
			'chr12' => 132349534,
			'chr13' => 114142980,
			'chr13_random' => 186858,
			'chr14' => 106368585,
			'chr15' => 100338915,
			'chr15_random' => 784346,
			'chr16' => 88827254,
			'chr16_random' => 105485,
			'chr17' => 78774742,
			'chr17_random' => 2617613,
			'chr18' => 76117153,
			'chr18_random' => 4262,
			'chr19' => 63811651,
			'chr19_random' => 301858,
			'chr20' => 62435964,
			'chr21' => 46944323,
			'chr21_random' => 1679693,
			'chr22' => 49691432,
			'chr22_random' => 257318,
			'chrX' => 154913754,
			'chrX_random' => 1719168,
			'chrY' => 57772954,
			'chrM' => 16571

);
	
# Status
%array_status = (
	"TRUE"   		=> {
					desc => 'Validated',
					color => '13,219,19',
					},
	"FALSE"  		=> {
					desc => 'False',
					color => '244,43,13',
					},
	"Ambiguous/FALSE"  	=> {
					desc => 'Ambiguous/False',
					color => '244,43,13',
					},
	"FILTERED OUT"  	=> {
					desc => 'Unreliable prediction',
					color => '189,185,184',
					},
	"ND" 			=> {
					desc => 'Predicted',
					color => '2,2,19',
					},
	"WITHDRAWN"		=> {
					desc => 'Obsolete',
					color => '189,185,184',
					},
	"Withdrawn"		=> {
					desc => 'Obsolete',
					color => '189,185,184',
					},
	"withdrawn"		=> {
					desc => 'Obsolete',
					color => '189,185,184',
					},     
	"AMBIGUOUS"  		=> {
					desc => 'Ambiguous',
					color => '9,16,240',
					},
	"Ambiguous"  		=> {
					desc => 'Ambiguous',
					color => '9,16,240',
					},
	"ambiguous"  		=> {
					desc => 'Ambiguous',
					color => '9,16,240',
					},
	"possible_TRUE"		=> {
					desc => 'Predicted',
					color => '2,2,19',
					},
	"possible_FALSE"	=> {
					desc => 'Predicted',
					color => '2,2,19',
					},
);

# Predictions
open(OUTFILE, '>ucsctracks_predictions.bed');
print OUTFILE "browser position chr17:40,565,780-42,502,878
browser hide all
";	

$select = "SELECT DISTINCT p.research_name, i.name, p.chr, p.BP1s AS bp1_start, p.BP1e AS bp1_end, p.BP2s AS bp2_start, p.BP2e AS bp2_end
	FROM predictions p, inversions i WHERE p.inv_id=i.id AND p.chr != '' AND i.status != 'withdrawn'";
$dbh->{LongReadLen} = 64000;
$dbh->{LongTruncOk} = 1;
$sth = $dbh->prepare($select);
$sth->execute();

print OUTFILE "track name=\"Predictions\" description=\"InvFEST: Predictions of inversions from individual studies\" visibility=2 color=246,206,4\n";

while($invinfo=$sth->fetchrow_hashref()) {

	# Repare inconsistences #####################################################################
	#    Chromosome name
	if (exists $EndOfChromosomes{ $invinfo->{chr} }) {
	} else {   # Cannot be repaired --> alert and next
		print "\tERROR: Chromosome " . $EndOfChromosomes{ $invinfo->{chr} } . " doesn't exist!\n" ;
		next;
	};
	
	#    Start > End in BP1
	if ($invinfo->{bp1_start} > $invinfo->{bp1_end}) {
		print "\tWARNING: Start > End in BP1\n";
		$origStart = $invinfo->{bp1_start};
		$origEnd = $invinfo->{bp1_end};
		$invinfo->{bp1_start} = $origEnd;
		$invinfo->{bp1_end} = $origStart;
	}
	
	#    Start > End in BP2
	if ($invinfo->{bp2_start} > $invinfo->{bp2_end}) {
		print "\tWARNING: Start > End in BP2\n";
		$origStart = $invinfo->{bp2_start};
		$origEnd = $invinfo->{bp2_end};
		$invinfo->{bp2_start} = $origEnd;
		$invinfo->{bp2_end} = $origStart;
	}
	
	#    Start coordinate
	if ($invinfo->{bp1_start} < 1) {
		if ($invinfo->{bp1_end} < 1) {
			print "\tERROR: Both BP1 coordinates < 1\n";
			next;
		}
		print "\tWARNING: Start coordinate " . $invinfo->{bp1_start} . " < 1\n";
		$invinfo->{bp1_start} = 1;
	}
	
	#    End coordinate
	if ($invinfo->{bp2_end} > $EndOfChromosomes{ $invinfo->{chr} }) {
		if ($invinfo->{bp2_start} > $EndOfChromosomes{ $invinfo->{chr} }) {
			print "\tERROR: Both BP2 coordinates > chromosome range\n";
			next;
		}
		print "\tWARNING: End coordinate " . $invinfo->{bp2_end} . " > chromosome range\n";
		$invinfo->{bp2_end} = $EndOfChromosomes{ $invinfo->{chr} };
	}
	
	###############################################################################################
	
	# Save to file
	$id = $invinfo->{name}.",".$invinfo->{research_name};
	$id =~ s/ et al. //;
	$id =~ s/\s+//g;
	
	print OUTFILE $invinfo->{chr}."\t".($invinfo->{bp1_start}-1)."\t".$invinfo->{bp2_end}."\t".$id.
	"\t0\t+\t0\t0\t246,206,4\t2\t".($invinfo->{bp1_end}-$invinfo->{bp1_start}+1).','.
	($invinfo->{bp2_end}-$invinfo->{bp2_start}+1)."\t0,".($invinfo->{bp2_start}-$invinfo->{bp1_start})."\n";

}

$sth->finish();

close OUTFILE;


# Inversions
open(OUTFILE, '>ucsctracks_inversions.bed');
print OUTFILE "browser position chr17:40,565,780-42,502,878
browser hide all
";	

$select = "SELECT DISTINCT i.name, i.chr, i.status, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end 
FROM inversions i INNER JOIN breakpoints b ON b.id = (SELECT id FROM breakpoints b2 WHERE b2.inv_id=i.id
	ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.`date` DESC
	LIMIT 1) WHERE i.chr != '' AND i.status !='withdrawn'";
$dbh->{LongReadLen} = 64000;
$dbh->{LongTruncOk} = 1;
$sth = $dbh->prepare($select);
$sth->execute();

print OUTFILE "track name=\"InvFEST Inversions\" description=\"InvFEST: Non-redundant set of inversions\" visibility=2 color=157,4,246 itemRgb=\"On\"\n";

while($invinfo=$sth->fetchrow_hashref()) {

	# Repare inconsistences #####################################################################
	#    Chromosome name
	if (exists $EndOfChromosomes{ $invinfo->{chr} }) {
	} else {   # Cannot be repaired --> alert and next
		print "\tERROR: Chromosome " . $EndOfChromosomes{ $invinfo->{chr} } . " doesn't exist!\n" ;
		next;
	};
	
	#    Start > End in BP1
	if ($invinfo->{bp1_start} > $invinfo->{bp1_end}) {
		print "\tWARNING: Start > End in BP1\n";
		$origStart = $invinfo->{bp1_start};
		$origEnd = $invinfo->{bp1_end};
		$invinfo->{bp1_start} = $origEnd;
		$invinfo->{bp1_end} = $origStart;
	}
	
	#    Start > End in BP2
	if ($invinfo->{bp2_start} > $invinfo->{bp2_end}) {
		print "\tWARNING: Start > End in BP2\n";
		$origStart = $invinfo->{bp2_start};
		$origEnd = $invinfo->{bp2_end};
		$invinfo->{bp2_start} = $origEnd;
		$invinfo->{bp2_end} = $origStart;
	}
	
	#    Start coordinate
	if ($invinfo->{bp1_start} < 1) {
		if ($invinfo->{bp1_end} < 1) {
			print "\tERROR: Both BP1 coordinates < 1\n";
			next;
		}
		print "\tWARNING: Start coordinate " . $invinfo->{bp1_start} . " < 1\n";
		$invinfo->{bp1_start} = 1;
	}
	
	#    End coordinate
	if ($invinfo->{bp2_end} > $EndOfChromosomes{ $invinfo->{chr} }) {
		if ($invinfo->{bp2_start} > $EndOfChromosomes{ $invinfo->{chr} }) {
			print "\tERROR: Both BP2 coordinates > chromosome range\n";
			next;
		}
		print "\tWARNING: End coordinate " . $invinfo->{bp2_end} . " > chromosome range\n";
		$invinfo->{bp2_end} = $EndOfChromosomes{ $invinfo->{chr} };
	}
	
	###############################################################################################
	
	# Save to file
	$id = $invinfo->{name}.":".$array_status{$invinfo->{status}}{desc};
	$id =~ s/\s+//g;
		
	print OUTFILE $invinfo->{chr}."\t".($invinfo->{bp1_start}-1)."\t".$invinfo->{bp2_end}."\t".$id.
	"\t0\t+\t0\t0\t".$array_status{$invinfo->{status}}{color}."\t2\t".($invinfo->{bp1_end}-$invinfo->{bp1_start}+1).','.
	($invinfo->{bp2_end}-$invinfo->{bp2_start}+1)."\t0,".($invinfo->{bp2_start}-$invinfo->{bp1_start})."\n";

}

$sth->finish();

close OUTFILE;
