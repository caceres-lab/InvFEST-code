#!/usr/bin/perl
#------------------------------------------------------------------------------------
# Copyright (C) 2014, by the authors,  All rights reserved.
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#	
# Name:      	generateRepeatTracks.pl 
#
# Author(s): 	Alexander Martínez-Fundichely & Sònia Casillas
#		Comparative and Functional Genomics group
#		Institut de Biotecnologia i de Biomedicina
#		Universitat Autònoma de Barcelona, Spain
#
# Contact:	batscherow@gmail.com, sonia.casillas@uab.cat
#
#     Status.......: 3.1 (Released)
#     Status Date..: 01.04.2014
#     Purpose......: generate files for options DISCARD/TAG_MAPPINGS_IN_REPEATS/SEGDUPS by GRIAL.pl
#
# Changes   Date(DD.MM.YYYY)   NAME   DESCRIPTION
#
#------------------------------------------------------------------------------------

use DBI;
use DBD::mysql;

### READ CONFIG FILE ----------------------------------------------------------------
read_config_file($ARGV[0]);


### CHECK IF YOU REALLY NEED THESE FILES --------------------------------------------

if (($GENERATE_REPEATS_TRACK eq 'no') and ($GENERATE_SEGDUPS_TRACK eq 'no')) {

	die ("ERROR: You don't need the repeats track nor the segdups track according to your parameters in the configuration file. These files will not be generated unless any of DISCARD_MAPPINGS_IN_REPEATS, TAG_MAPPINGS_IN_REPEATS, DISCARD_MAPPINGS_IN_SEGDUPS, or TAG_MAPPINGS_IN_SEGDUPS is set to 'yes'\n\n");

}


### GET CHROMOSOMES FOR THE CURRENT ASSEMBLY ----------------------------------------

$CHR_COORDS=''; @MYCHROMOSOMES = ();

# Coordinates in chromosomes (automatically retrieved)
@fetchChromSizes = `./fetchChromSizes $ASSEMBLY`;

foreach my $fetchChromSizes (@fetchChromSizes) {

	if ($fetchChromSizes =~ /^(\S+)\t(\S+)/) {   # ^\|\s+(\S+)\s+\|\s+(\d+)\s+\|

		$thisCHROM = $1;
		$thisSIZE = $2;

		# Filter out chromosomes not in the chromosomes-to-analyze list
		if (scalar(@CHROMOSOMES) > 0) {
			next unless (exists $CHROMOSOMES{$thisCHROM});
		}		
		
		# Filter out random chromosomes if requested
		next if (($ANALYZE_RANDOM_CHROMOSOMES eq 'no') and ($thisCHROM =~ /random/));
		
		# Otherwise, store current chromosome and size
		$CHR_COORDS->{$ASSEMBLY}->{$thisCHROM} = $thisSIZE;
		push(@MYCHROMOSOMES, $thisCHROM);
				
	}

}


### SET PARAMETERS ------------------------------------------------------------------

%codesRepeats = (
	'SINE' =>		'S',
	'LINE' =>		'L',
	'LTR' =>		'T',
	'DNA' =>		'D',
	'Simple_repeat' =>	'I',
	'Low_complexity' =>	'X',
	'Unknown' =>		'U',
	'snRNA' =>		'R',
	'Other' =>		'O',
	'tRNA' =>		'R',
	'Satellite' =>		'E',
	'rRNA' =>		'R',
	'RC' =>			'C',
	'DNA?' =>		'd',
	'srpRNA' =>		'R',
	'scRNA' =>		'R',
	'RNA' =>		'R',
	'SINE?' =>		's',
	'LTR?' =>		't',
	'Unknown?' =>		'u',
	'LINE?' =>		'l',
);

######### MYSQL QUERIES    ARE OPTIMIZED FOR HG18 & HG19. YOU MIGHT #################
######### NEED TO CHANGE THEM FOR OTHER ASSEMBLIES ACCORDING TO THE #################
######### APPROPRIATE UCSC DATABASE STRUCTURE.                      #################

if ($ASSEMBLY eq 'hg18') {

	$SELECT_SEGDUPS = "SELECT DISTINCTROW chromStart, chromEnd, otherChrom, 
				otherStart, otherEnd 
				FROM genomicSuperDups WHERE chrom = 'CHROM'";
	
	$SELECT_REPEATS = "SELECT DISTINCTROW genoStart, genoEnd, repClass 
					FROM CHROM_rmskRM327";

} elsif ($ASSEMBLY eq 'hg19') {

	$SELECT_SEGDUPS = "SELECT DISTINCTROW chromStart, chromEnd, otherChrom, 
				otherStart, otherEnd 
				FROM genomicSuperDups WHERE chrom = 'CHROM'";
	
	$SELECT_REPEATS = "SELECT DISTINCTROW genoStart, genoEnd, repClass 
					FROM rmsk WHERE genoName = 'CHROM'";

} else {

	$SELECT_SEGDUPS = "";
	$SELECT_REPEATS = "";
	
	die ("ERROR: The $ASSEMBLY assembly is not fully considered in our script. Please consult the UCSC database structure for this assembly and edit lines 122 and 123 of this script to provide proper MySQL queries. You can see example queries in lines 104-118 of the script. Please note the use of CHROM as a general nomenclature that later on the script will be changed to each chromosome name; please follow the same nomenclature. You can contact us at batscherow\@gmail.com or sonia.casillas\@uab.cat if you prefer that we optimize this script for you.\n\n");

}

#####################################################################################
#####################################################################################
#####################################################################################


### CHECK IF FILES ALREADY EXIST; GENERATE NECESSARY FILES --------------------------

## Fork chromosomes
my @childs_fork1 = ();

foreach my $chromosome (@MYCHROMOSOMES) {

	my $pid_fork1 = fork();
	
	if ($pid_fork1) {
		
		# parent
		push(@childs_fork1, $pid_fork1);
		
	} elsif ($pid_fork1 == 0) { 
		
		my $track_segdups = '';
		my $track_repeats = '';

		unless ((($GENERATE_SEGDUPS_TRACK eq 'no') or (-e "$REPEATFILES_PATH$ASSEMBLY.$chromosome.segdups")) and
		(($GENERATE_REPEATS_TRACK eq 'no') or (-e "$REPEATFILES_PATH$ASSEMBLY.$chromosome.repeats"))) {
		
			# Connect to UCSC database
			my $host = "genome-mysql.cse.ucsc.edu";
			my $user = "genome";
			my $pass = "";
			my $database = $ASSEMBLY;
			my $dsn = "DBI:mysql:host=$host;port=3306";
			my $emsg= "Could not access the Database\n Could not open DSN $dsn"; 
			$dbh = DBI->connect($dsn, $user, $pass, {RaiseError => 1, 
				 PrintError => 1, 
				 AutoCommit => 1, 
				 LongReadLen => 4000}) || die("$emsg: " . $DBI::errstr .__LINE__."\n");
			$dbh->do("USE `$database`");
			
		}
		

		# SEGDUPS TRACK -----------------------------------------------------
		
		# Check if track is required and if it already exists in REPEATFILES_PATH
		unless (($GENERATE_SEGDUPS_TRACK eq 'no') or (-e "$REPEATFILES_PATH$ASSEMBLY.$chromosome.segdups")) {
		
			# Open the file to store the track
			open(FILESEGDUPS, ">$REPEATFILES_PATH$ASSEMBLY.$chromosome.segdups");
			
			# Create the track
			$track_segdups = '-' x getLimits($ASSEMBLY, $chromosome);
		
			# Mask Segmental Duplications on the track
			$THIS_SELECT_SEGDUPS = $SELECT_SEGDUPS;
			$THIS_SELECT_SEGDUPS =~ s/CHROM/$chromosome/g; 
			$dbh->{LongReadLen} = 64000;
			$dbh->{LongTruncOk} = 1;
			$sth_segdups = $dbh->prepare($THIS_SELECT_SEGDUPS);
			$sth_segdups->execute();

			while ($segdup = $sth_segdups->fetchrow_hashref()) {
	
				substr $track_segdups, $segdup->{chromStart}, ($segdup->{chromEnd}-$segdup->{chromStart}), ('G' x ($segdup->{chromEnd}-$segdup->{chromStart}));
	
			}
	
			$sth_segdups->finish();

			# Print track on file, and close file
			print FILESEGDUPS $track_segdups;
			close FILESEGDUPS;	

		}


		# REPEATS TRACK -----------------------------------------------------

		unless (($GENERATE_REPEATS_TRACK eq 'no') or (-e "$REPEATFILES_PATH$ASSEMBLY.$chromosome.repeats")) {

			# Open the file to store the track
			open(FILEREPEATS, ">$REPEATFILES_PATH$ASSEMBLY.$chromosome.repeats");
		
			# Create the track
			$track_repeats = '-' x getLimits($ASSEMBLY, $chromosome);

			# Mask Repeats on the track
			$THIS_SELECT_REPEATS = $SELECT_REPEATS;
			$THIS_SELECT_REPEATS =~ s/CHROM/$chromosome/g;
			$dbh->{LongReadLen} = 64000;
			$dbh->{LongTruncOk} = 1;
			$sth_repeats = $dbh->prepare($THIS_SELECT_REPEATS);
			$sth_repeats->execute();

			while ($repeat = $sth_repeats->fetchrow_hashref()) {
	
				my $codeHere;
				if (exists $codesRepeats{$repeat->{repClass}}) {
					$codeHere = $codesRepeats{$repeat->{repClass}};
				} else {
					$codeHere = 'o';
				}
	
				substr $track_repeats, $repeat->{genoStart}, ($repeat->{genoEnd}-$repeat->{genoStart}), ($codeHere x ($repeat->{genoEnd}-$repeat->{genoStart}));
	
			}
	
			$sth_repeats->finish();	
	
			# Print track on file, and close file
			print FILEREPEATS $track_repeats;
			close FILEREPEATS;

		}
		
		print "$chromosome finished!\n";
		
		$dbh->disconnect;

		# Always terminate fork!
		exit(0);
			
	} else {

		die "couldn’t fork $chromosome: $!\n";
	
	}		

}

# Wait results for both strands to finish!
foreach (@childs_fork1) {

	waitpid($_, 0);

}

print "Done!!!\n\n";
exit;

### END OF SCRIPT -------------------------------------------------------------------

### ---------------------------------------------------------------------------------
###	SUBROUTINES 
### ---------------------------------------------------------------------------------

sub read_config_file {

	my($filename) = @_;

	if ($filename eq '') {
		$filename = 'GRIAL.config';
	}
	
	open(CONFIGFILE, $filename) or die ("\nERROR: Could not read configuration file ($filename)!\n\n");
	foreach my $line (<CONFIGFILE>) {
		next if (($line =~ /^#/) or ($line !~ /([^=]+)=([^=]+)\s*/));
		my($var, $value) = ($line =~ /([^=]+)=([^=]+)\s*/);
		chomp $value;
		if ($var =~ /DUPLICATEPEMS_BP_(.+)/) {
			$DUPLICATEPEMS_BP_LIBRARY{$1} = $value;
		} else {
			$$var = $value;
		}
	}
	close(CONFIGFILE);
	
	
	# Checks before start & set defaults when parameters are invalid
	
	if (-d $REPEATFILES_PATH){
		if ($REPEATFILES_PATH !~ /\/$/) {
			$REPEATFILES_PATH .= '/';
		}
	} else {
		$REPEATFILES_PATH = '';
	    	print "WARNING: REPEATFILES_PATH was invalid -> set to current working path (".getcwd().")\n";
	}	
	
	
	if ($ASSEMBLY eq '') {
		die ("ERROR: You need to specify a valid ASSEMBLY code\n");
	}

	unless (($CHROMOSOMES eq 'all') or ($CHROMOSOMES eq '')) {
		@CHROMOSOMES = split /[,\s]+/, $CHROMOSOMES;
		%CHROMOSOMES = map { $_ => undef } @CHROMOSOMES;
		print "These chromosomes will be analyzed: ".(join(', ', @CHROMOSOMES))."\n";
	}
	
	if ($ANALYZE_RANDOM_CHROMOSOMES !~ /^(yes|no)$/) {
		$ANALYZE_RANDOM_CHROMOSOMES = 'no';
		print "WARNING: ANALYZE_RANDOM_CHROMOSOMES was not provided or invalid -> set to default (no)\n";
	}
	
	if (($DISCARD_MAPPINGS_IN_REPEATS ne 'no') or ($TAG_MAPPINGS_IN_REPEATS ne 'no') or ($MAPABILITY_REPEATS ne 'no')) {
	
		$GENERATE_REPEATS_TRACK = 'yes';
	
	} else {
	
		$GENERATE_REPEATS_TRACK = 'no';
	
	}
	
	if (($DISCARD_MAPPINGS_IN_SEGDUPS ne 'no') or ($TAG_MAPPINGS_IN_SEGDUPS ne 'no') or ($MAPABILITY_SEGDUPS ne 'no')) {
	
		$GENERATE_SEGDUPS_TRACK = 'yes';
	
	} else {
	
		$GENERATE_SEGDUPS_TRACK = 'no';
	
	}
	
}

sub getLimits {

	my($ASSEMBLY, $chromosome) = @_;

	return($CHR_COORDS->{$ASSEMBLY}->{$chromosome});
}

