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
# Name:      	GRIALscore.pl 
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
#     Purpose......: score predicted inversions by GRIAL.pl
#
# Changes   Date(DD.MM.YYYY)   NAME   DESCRIPTION
#
#------------------------------------------------------------------------------------

use List::Util qw[min max];
use Math::CDF qw[ppois pbinom];
use Number::Interval;
use DBI;
use DBD::mysql;

print "\nSTART GRIALscore.pl to score inversions predicted by GRIAL... \n";

### READ CONFIG FILE ----------------------------------------------------------------
read_config_file($ARGV[0]);

### SET STATISTICS OF THE CONCORDANT PEMs -------------------------------------------
concordant_statistics();

# Retrieve all files from path
@files = retrieve_GRIAL_output(); 

# Fork chromosomes
print "\nNow separate chromosomes will be analyzed in parallel. Forking the process...\n";
my @childs_fork1 = ();

# Parse each chromosome file
CHROMOSOME: foreach $file (@files) {

	my $pid_fork1 = fork();
	
	if ($pid_fork1) {
		
		# parent
		push(@childs_fork1, $pid_fork1);
		
	} elsif ($pid_fork1 == 0) { 
	

	# Prepare this chromosome data
	# ----------------------------
	($chromosome) = ($file =~ /(chr.+)\.inversions\.$BP_DEFHERE/);
	print "$chromosome - Started... \n";
	
	if (($ANALYZE_RANDOM_CHROMOSOMES eq 'no') and ($chromosome =~ /random/)) {
		print "\t$chromosome - Skipping this random chromosome!\n\n";
		next CHROMOSOME;
	}
	
	# Open output file. It will contain all inversions with probability values 
	# ---------------------------------------	
	open(OUTFILE, '>'.$OUTPUT_PATH.$chromosome.'.inversions.'.$BP_DEFHERE.'.genotyped'); 
	
	# Read repeats track and segdups track
	# ---------------------------------------
	if (($MAPABILITY_REPEATS eq 'yes') or ($DISCARD_MAPPINGS_IN_REPEATS eq 'yes')) {
	
		readRepeatsTrack($chromosome, $ASSEMBLY);
		
	}
	
	if ($DISCARD_MAPPINGS_IN_SEGDUPS eq 'yes') {
	
		readSegdupsTrack($chromosome, $ASSEMBLY);
		
	}
	
	# Read inversions from this chromosome
	# ---------------------------------------
#	print "\t$chromosome - Reading inversions...\n";
	open(INVFILE, $OUTPUT_PATH.$file) or die ("Can't read input Inversion file: $!");
	
	@inversions=();
	INVERSION: while (<INVFILE>) {
		
		# Read this inversion data
		($invInfo) = readInvInfo($_);
		
		next if (
			($invInfo->{posWeighted} + $invInfo->{negWeighted} < $MIN_SUPPORT_INVERSIONS) 
			or 
			(($ALLOW_SINGLE_CLUSTER_INVERSIONS eq 'yes') and (($invInfo->{posWeighted} < $MIN_SUPPORT_CLUSTERS) and ($invInfo->{negWeighted} < $MIN_SUPPORT_CLUSTERS))) 
			or
			(($ALLOW_SINGLE_CLUSTER_INVERSIONS eq 'no') and (($invInfo->{posWeighted} < $MIN_SUPPORT_CLUSTERS) or ($invInfo->{negWeighted} < $MIN_SUPPORT_CLUSTERS))) 
			);
	
		push(@inversions, $invInfo);

	}	
	close INVFILE;
#	print "\t\t$chromosome - Inversions correctly uploaded!\n";

	# Process discordants data in chromosome
	# ---------------------------------------
	($libraryStatistics) = readDiscordants($chromosome);
	
	# Process concordants data in chromosome
	# ---------------------------------------
#	print "\t$chromosome - Reading concordant data for this chromosome...\n\t(This step may take long time; please be patient)\n";

	($libraryStatistics) = readConcordants($chromosome, $libraryStatistics);

#	print "\t\t$chromosome - Concordant data correctly uploaded!\n";

	# Calculate PEs per bp in whole chr
	# ---------------------------------------
	($libraryStatistics) = chrSize($chromosome, $ASSEMBLY, $libraryStatistics);
	
	# Compute alfa parameter for std/inv likelihood 
	# ---------------------------------------
	$alfa = compute_alfa();

	# Compute scores on inversions
	# ---------------------------------------
#	print "\t$chromosome - Computing scores on inversions...\n";

	foreach $invInfo (@inversions) {

		compute_likelihood($invInfo);
		compute_score($invInfo); 
		compute_theoreticalscores($invInfo, $libraryStatistics);
	
		print OUTFILE printFile($invInfo);
	
	}

#	print "\t\t$chromosome - Scores correctly computed!\n\n";
	print "\t$chromosome - Finished!\n";
	
	close OUTFILE;
	

	exit(0);
			
	} else {

		die "couldn’t fork $chromosome: $!\n";
	
	}
	
	
}


# Wait results for all chromosomes to finish!
foreach (@childs_fork1) {

	waitpid($_, 0);

}


print "\nSCORING INVERSIONS IS FINISHED. Now exiting the script...\n\n";

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

	open(CONFIGFILE, $filename) or die ("\nERROR: Could not read configuration file!\n\n");
	foreach my $line (<CONFIGFILE>) {
		next if (($line =~ /^#/) or ($line !~ /=/));
		my($var, $value) = ($line =~ /([^=]+)=([^=]+)\s+/);
		if ($var =~ /DUPLICATEPEMS_BP_(.+)/) {
			$DUPLICATEPEMS_BP_LIBRARY{$1} = $value;
		} else {
			$$var = $value;
		}
	}
	close(CONFIGFILE);

	# Checks before start & set defaults when parameters are invalid
	unless (-e $DISCORDANTS_FILENAME) { 
		die("ERROR: Please provide a valid discordants filename (DISCORDANTS_FILENAME)!\n\n");
	}

	if ($CONCORDANTS !~ /^[01]$/) {

		$CONCORDANTS = 0;

	} elsif ($CONCORDANTS == 1) {

		if ($CONCORDANTS_FILENAME_PREFIX eq '') {
			die("ERROR: Please provide a valid concordants filename (CONCORDANTS_FILENAME_PREFIX)!\n\n");
		}

	} else {}
	
	if ($CONCORDANTS_FILENAME_PREFIX eq '') {
		die "ERROR: CONCORDANTS_FILENAME_PREFIX was not provided or is invalid, and it is necessary for the estimation of GRIAL scores. Exiting the program!\n\n";
	} 
	
	if ($MIN_SUPPORT_CLUSTERS !~ /^\d+$/) {
		$MIN_SUPPORT_CLUSTERS = 1;
		print "WARNING: MIN_SUPPORT_CLUSTERS was not provided or is invalid -> set to default (1 PEM per valid cluster)\n";
	}

	if ($MIN_SUPPORT_INVERSIONS !~ /^\d+$/) {
		$MIN_SUPPORT_INVERSIONS = 2;
		print "\nWARNING: Minimum support for inversions was not provided or is invalid -> set to default (2 PEMs in total per valid inversion)\n";
	}
	
	if ($ALLOW_SINGLE_CLUSTER_INVERSIONS !~ /^(yes|no)$/) {
		$ALLOW_SINGLE_CLUSTER_INVERSIONS = 'yes';
		print "WARNING: ALLOW_SINGLE_CLUSTER_INVERSIONS was not provided or is invalid -> set to default (yes)\n";
	}
	
	if (-d $OUTPUT_PATH){
		if ($OUTPUT_PATH !~ /\/$/) {
			$OUTPUT_PATH .= '/';
		}
	} else {
		$OUTPUT_PATH = '';
	    	print "\nWARNING: Output path was invalid -> set to current working path (".getcwd().")\n";
	}	

	if ($BP_DEFINITION !~ /^(wide|narrow|all)$/) {
		$BP_DEFINITION = 'all';
		$BP_DEFHERE = 'narrow';
		print "WARNING: BP_DEFINITION was not provided or is invalid -> set to default (all)\n";
		
	} else {
	
		if ($BP_DEFINITION eq 'wide') {
		
			$BP_DEFHERE = 'wide';
		
		} else {
		
			$BP_DEFHERE = 'narrow';
		
		} 
	
	}
	
	unless (($CHROMOSOMES eq 'all') or ($CHROMOSOMES eq '')) {
		@CHROMOSOMES = split /[,\s]+/, $CHROMOSOMES;
		print "\nThese chromosomes will be analyzed: ".(join(', ', @CHROMOSOMES))."\n";
	}
	
	if ($ANALYZE_RANDOM_CHROMOSOMES !~ /^(yes|no)$/) {
		$ANALYZE_RANDOM_CHROMOSOMES = 'no';
		print "WARNING: ANALYZE_RANDOM_CHROMOSOMES was not provided or is invalid -> set to default (no)\n";
	}

	if ($MAPABILITY_REPEATS !~ /^(yes|no)$/) {
		$MAPABILITY_REPEATS = 'no';
		print "WARNING: MAPABILITY_REPEATS was not provided or is invalid -> set to default (no)\n";
	} 
	
	if ($MAPABILITY_SEGDUPS !~ /^(yes|no)$/) {
		$MAPABILITY_SEGDUPS = 'no';
		print "WARNING: MAPABILITY_SEGDUPS was not provided or is invalid -> set to default (no)\n";
	} 

	if (($MAPABILITY_REPEATS eq 'yes') or ($MAPABILITY_SEGDUPS eq 'yes')) {
	
		if ($REPEATFILES_PATH eq '') {
			die "ERROR: REPEATFILES_PATH was not provided or is invalid, and it is necessary for the calculation of mapability for GRIAL scores. Please provide it or choose MAPABILITY=no. Exiting the program!\n\n";
		} else {
		
			if ($REPEATFILES_PATH !~ /\/$/) {
			
				$REPEATFILES_PATH .= '/';
			
			}
		
		}
		
		if ($ASSEMBLY eq '') {
			die "ERROR: ASSEMBLY was not provided or is invalid, and it is necessary for the calculation of mapability for GRIAL scores. Please provide it or choose MAPABILITY=no. Exiting the program!\n\n";
		} 
		
		if ($MAPABILITY_SEGDUPS eq 'yes') {

			if (($ASSEMBLY ne 'hg18') and ($ASSEMBLY ne 'hg19')) {
		
				die ("ERROR: The $ASSEMBLY assembly is not fully considered in our script. Please consult the UCSC database structure for this assembly and edit lines 900 and 909 of script GRIALscore.pl to provide a proper MySQL query. You can contact us at batscherow\@gmail.com or sonia.casillas\@uab.cat if you prefer that we optimize this script for you, or you can choose MAPABILITY_SEGDUPS=no in the configuration file to skip this step.\n\n");
			
			}
			
		}
		
	}

	if ($MAPABILITY_FRAGMENT_SIZE !~ /^\d+\.?\d*$/) {
		$MAPABILITY_FRAGMENT_SIZE = 400;
		print "WARNING: MAPABILITY_FRAGMENT_SIZE was not provided or is invalid -> set to default (400)\n";
	}

	if ($MAPABILITY_NUM_CHANGES !~ /^\d+\.?\d*$/) {
		$MAPABILITY_NUM_CHANGES = 2;
		print "WARNING: MAPABILITY_NUM_CHANGES was not provided or is invalid -> set to default (2)\n";
	}

	if ($MAPABILITY_REPEATS_IDENTITY !~ /^\d+\.?\d*$/) {
		$MAPABILITY_REPEATS_IDENTITY = 0.99;
		print "WARNING: MAPABILITY_REPEATS_IDENTITY was not provided or is invalid -> set to default (0.99)\n";
	}

	if ($MAPABILITY_SEGDUPS_CORE !~ /^\d+\.?\d*$/) {
		$MAPABILITY_SEGDUPS_CORE = 0.8;
		print "WARNING: MAPABILITY_SEGDUPS_CORE was not provided or is invalid -> set to default (0.8)\n";
	}

	if ($MAPABILITY_SEGDUPS_CHANGES_CORE !~ /^\d+\.?\d*$/) {
		$MAPABILITY_SEGDUPS_CHANGES_CORE = 0.5;
		print "WARNING: MAPABILITY_SEGDUPS_CHANGES_CORE was not provided or is invalid -> set to default (0.5)\n";
	}
	
}

### ---------------------------------------------------------------------------------

sub concordant_statistics {

	if ($CONCORDANTS == 1) {
	
		# Calculate on-the-fly from the concordant PEMs:
		get_DATA_STAT();
		
	} else {
	
		# Instead, set statistics from config file:
		%DATA_STAT = ();
		
		# I'll need the AVG of the library for sure
		if ($CONCORDANTS_AVG =~ /^\d+\.?\d*$/) {
			
			$DATA_STAT{mean} = $CONCORDANTS_AVG;
			
		} else {
		
			print "\nERROR: Please provide valid AVG!\n\n";
			exit;
			
		}
		
		# Calculate MIN / MAX values
		if (($CONCORDANTS_MIN !~ /^\d+\.?\d*$/) or ($CONCORDANTS_MAX !~ /^\d+\.?\d*$/)) {
		
			# I need STD or VAR
			if ($CONCORDANTS_VAR =~ /^\d+\.?\d*$/) {
					$DATA_STAT{var} = $CONCORDANTS_VAR;
					$DATA_STAT{std} = sqrt $CONCORDANTS_VAR;
			} else {
				if ($CONCORDANTS_STD =~ /^\d+\.?\d*$/) {
					$DATA_STAT{std} = $CONCORDANTS_STD;
					$DATA_STAT{var} = $CONCORDANTS_STD**2;	
				} else {
					print "\nERROR: Please provide valid VAR or STD!\n\n";
					exit;			
				}
			}
			
			# I also need AVG and XSTD
			if ($CONCORDANTS_XSTD !~ /^\d+\.?\d*$/) {
				print "\nERROR: Please provide valid AVG+XSTD or MIN+MAX!\n\n";
				exit;
			} else {
				$DATA_STAT{mean} = $CONCORDANTS_AVG;
				$DATA_STAT{min} = $CONCORDANTS_AVG - $CONCORDANTS_XSTD*$DATA_STAT{std};
				$DATA_STAT{max} = $CONCORDANTS_AVG + $CONCORDANTS_XSTD*$DATA_STAT{std};
			}
		
		} 
		
		# Rewrite MIN / MAX values if they are valid values
		if ($CONCORDANTS_MIN =~ /^\d+\.?\d*$/) {
		
			$DATA_STAT{min} = $CONCORDANTS_MIN;
				
		}
		
		if ($CONCORDANTS_MAX =~ /^\d+\.?\d*$/) {
		
			$DATA_STAT{max} = $CONCORDANTS_MAX;
				
		}		
		
	}
	
	# Calculate other values that I will need, from the above (range and errors above and below the avg)
	$DATA_STAT{range} = $DATA_STAT{max} - $DATA_STAT{min};
	$DATA_STAT{errorAboveAvg} = $DATA_STAT{max} - $DATA_STAT{mean};
	$DATA_STAT{errorBelowAvg} = $DATA_STAT{mean} - $DATA_STAT{min};	

}

### ---------------------------------------------------------------------------------

sub get_DATA_STAT {

	print "Reading concordant mappings to compute library metrics...\n(This step may take long time; please be patient)\n";
	
	my @concordant_files_list = `ls $CONCORDANTS_FILENAME_PREFIX.*`;
	my @fragment_lenght_list = ();
	
	foreach my $file (@concordant_files_list) {

		open(DATA_FILE, $file) or die ("\nERROR: Could not read concordants file!\n\n");
		push(@fragment_lenght_list, map { fragment_lenght($_)  } <DATA_FILE>);
		close DATA_FILE;
	}
	
	my $stat = Statistics::Descriptive::Full->new();
	$stat->add_data(@fragment_lenght_list);
	$DATA_STAT{var} = $stat->variance();
	$DATA_STAT{mean} = $stat->mean();
	$DATA_STAT{min} = $stat->min();
	$DATA_STAT{max} = $stat->max();
	$DATA_STAT{range} = $stat->sample_range();
	$DATA_STAT{std} = $stat->standard_deviation();
	
	print "Computed concordant metrics: \n\tMean=".$DATA_STAT{mean}."\n\tVar=".$DATA_STAT{var}."\n\tMin=".$DATA_STAT{min}."\n\tMax=".$DATA_STAT{max}."\n\tRange=".$DATA_STAT{range}."\n";
	
}

### ---------------------------------------------------------------------------------

sub retrieve_GRIAL_output {

	if ($CHROMOSOMES eq 'all') {
	
		$command = 'ls -1 '.$OUTPUT_PATH.' | grep -oP "^.*\.inversions\.'.$BP_DEFHERE.'$"'; 
		$allFiles = `$command`;
		@files = split /\n/, $allFiles;

	} else {
		
		foreach my $chromosome (@CHROMOSOMES) {	
			
			push(@files, $chromosome.'.inversions.'.$BP_DEFHERE);
			
		}

	}
	
 return @files;
 
}

### ---------------------------------------------------------------------------------

sub readInvInfo {

	my($line) = @_;
	my $invInfo;
	
	chomp $line;
	my($tag) = ($line =~ /^[^\s\d\w]*([\dNA]+\t[\dNA]+\t)/);
	my @columns = split /\t/, $line;
	my @supports = split /[\(\)\/]+/, $columns[8];
	
	$columns[0] =~ s/[^\d\w]+//;
	$columns[1] =~ s/[^\d\w]+//;
	
	$invInfo = {
		'posId' => $columns[0],
		'negId' => $columns[1],
		'chr' => $columns[2],
		'bp1_start' => $columns[4],
		'bp1_end' => $columns[5],
		'bp2_start' => $columns[6],
		'bp2_end' => $columns[7],
		'posSupport' => $supports[1],
		'posWeighted' => $supports[0],
		'negSupport' => $supports[3],
		'negWeighted' => $supports[2],
		'concordantsBridgeBP1' => 0,
		'concordantsBridgeBP2' => 0,
		'score_ratio' => 0,
		'score_test' => 0,
		'thscore_lambda' => 'NA',
		'thscore_lambda_BP1' => 'NA',
		'thscore_lambda_BP2' => 'NA',
		'thscore_mapability_BP1' => 'NA',
		'thscore_mapability_BP2' => 'NA',
		'thscore_BP1' => 'NA',
		'thscore_BP2' => 'NA',
		'thscore_all' => 'NA',
		'breakpoints' => $BP_DEFHERE,
		'supportingLibs' => '',
	};
	
	# Check if narrow BPs are congruent
	if (($invInfo->{bp1_start} > $invInfo->{bp1_end}) or ($invInfo->{bp2_start} > $invInfo->{bp2_end})) {
	
		die ("ERROR: Please run GRIAL.pl again with option BP_DEFINITION=all\n\n") unless (-e $OUTPUT_PATH.$invInfo->{chr}.'.inversions.wide');
		
		# Go and substitute by wide breakpoints
		my $command = 'grep -oP "^[^\s\d\w]*'.$tag.'.+" '.$OUTPUT_PATH.$invInfo->{chr}.'.inversions.wide';
		my $widecoords = `$command`;
		chomp $widecoords;
		
		my @columnsWide = split /\t/, $widecoords;
		$invInfo->{bp1_start} = $columnsWide[4];
		$invInfo->{bp1_end} = $columnsWide[5];
		$invInfo->{bp2_start} = $columnsWide[6];
		$invInfo->{bp2_end} = $columnsWide[7];
		$invInfo->{breakpoints} = 'wide';
	
	}
	
	# Define min and max size of inversion
	$invInfo->{min_invSize} = $invInfo->{bp2_start} - $invInfo->{bp1_end};
	$invInfo->{max_invSize} = $invInfo->{bp2_end} - $invInfo->{bp1_start};
	if ($invInfo->{min_invSize}<0) {
		$invInfo->{min_invSize}=0;
	}
	
	# I NEED TO KNOW WHICH LIBRARIES ARE SUPPORTING THE INVERSION
	my @supportingLibs = ();
	# I know IDs of the clusters (posId & negId)
	# First I get IDs of the PEMs corresponding to the clusters
	$posPEMids = ""; $negPEMids = "";
	unless ($invInfo->{posId} eq "NA") {
		my $command = 'grep -oP "'.$invInfo->{chr}.'\+\t'.$invInfo->{posId}.'\t[\d,]+" '.$OUTPUT_PATH.$invInfo->{chr}.'+.clusters';
		my $result = `$command`;
		($posPEMids) = ($result =~ /^\S+\t\S+\t([\d,]+)/);
	}

	unless ($invInfo->{negId} eq "NA") {
		my $command = 'grep -oP "'.$invInfo->{chr}.'-\t'.$invInfo->{negId}.'\t[\d,]+" '.$OUTPUT_PATH.$invInfo->{chr}.'-.clusters';
		my $result = `$command`;
		($negPEMids) = ($result =~ /^\S+\t\S+\t([\d,]+)/);
	}
	
	my @posPEMids = split /,/, $posPEMids;
	my @negPEMids = split /,/, $negPEMids;
		
	# Then I get libraries corresponding to the PEMids
	foreach $posPEMid (@posPEMids) {
	
		die ("ERROR: Please run GRIAL.pl again with option OUTPUT_PEM_INDEXES=yes\n\n") unless (-e $OUTPUT_PATH.$invInfo->{chr}.'+.PEMids');
		my $command = 'grep -oP "'.$invInfo->{chr}.'\+\t'.$posPEMid.'\t\w+" '.$OUTPUT_PATH.$invInfo->{chr}.'+.PEMids';
		my $result = `$command`;
		my ($lib) = ($result =~ /^\S+\t\S+\t(\w+)/);
		push(@supportingLibs, $lib);
	
	}
	
	foreach $negPEMid (@negPEMids) {
	
		die ("ERROR: Please run GRIAL.pl again with option OUTPUT_PEM_INDEXES=yes\n\n") unless (-e $OUTPUT_PATH.$invInfo->{chr}.'-.PEMids');
		my $command = 'grep -oP "'.$invInfo->{chr}.'-\t'.$negPEMid.'\t\w+" '.$OUTPUT_PATH.$invInfo->{chr}.'-.PEMids';
		my $result = `$command`;
		my ($lib) = ($result =~ /^\S+\t\S+\t(\w+)/);
		push(@supportingLibs, $lib);
	
	}
	
	# Finally I add the corresponding libraries to the array
	my %hash   = map { $_ => { disc => 0, conc => 0, likelihood => 0, genotype => 0,}, } @supportingLibs; 
	$invInfo->{supportingLibs} = \%hash;
	SUPPORTINGLIBS: foreach $supportingLib (keys %{$invInfo->{supportingLibs}}) {
		${$invInfo->{supportingLibs}}{$supportingLib}{disc} = scalar(grep(/^$supportingLib$/, @supportingLibs));
	}
	
	return($invInfo);

}

### ---------------------------------------------------------------------------------

sub readDiscordants {

	my($chromosome) = @_;
	
	my %libraryStatistics;
	
	my $command = 'awk \'{ print $1" "$3" "}\' '.$DISCORDANTS_FILENAME.' | grep \''.$chromosome.' \' | sort | uniq -c'; 	
	my $countResults = `$command`;
	@countResults = split /\n/, $countResults;
	
	foreach my $result (@countResults) {
	
		my($countItems, $countLib, $countChrom) = ($result =~ /\s+(\d+)\s+(\S+)\s+(\S+)\s+/);	
		$libraryStatistics{$countLib}{$countChrom}{disc} = $countItems;
	
	}	
	
	return(\%libraryStatistics);
	
}
	
### ---------------------------------------------------------------------------------

sub readConcordants {

	my($chromosome, $libraryStatistics) = @_;
	
	#foreach $library (@libraries) {
	
	open(CONCFILE, $CONCORDANTS_FILENAME_PREFIX.".".$chromosome) or die("No concordants file for chromosome $chromosome!\n");

	while (<CONCFILE>) {
	
		chomp $_;
		
		my($chrm, $start, $end, $Lread1, $Lread2, $library) = ($_ =~ /^(chr\S+)\s+(\d+)\s+(\d+)\s+\S+\s+\d+\s+\S\s+\d\s+\d+\s+\d+\s+(\d+)\s+(\d+)\s+.+\s+(\S+)$/);
		
		${$libraryStatistics}{$library}{$chrm}{conc}++;
			 
		my $start_l = $start; my $end_l = $start+$Lread1; my $start_r = $end-$Lread2; my $end_r = $end; 
	
		INVERSION: foreach $inv (@inversions) {
		
			#|# NOMÉS CONTAR EL CONCORDANT SI S D'UNA LLIBRERIA QUE SUPORTA LA INVERSIÓ AMB DISCORDANTS
			SUPPORTINGLIBS: foreach $supportingLib (keys %{$inv->{supportingLibs}}) {
				
				if ($supportingLib eq $library) {

					# concordant is bridging BP2	
					if (($chrm eq $inv->{chr}) and ($start_l > $inv->{bp1_end}) && ($end_l < $inv->{bp2_start}) && ($start_r > $inv->{bp2_end})) {
						$inv->{concordantsBridgeBP2}++;
						${$inv->{supportingLibs}}{$supportingLib}{conc}++;
					}
					
					# concordant is bridging BP1
					elsif (($chrm eq $inv->{chr}) and ($end_l < $inv->{bp1_start}) && ($start_r > $inv->{bp1_end}) && ($end_r < $inv->{bp2_start})) {
						$inv->{concordantsBridgeBP1}++;
						${$inv->{supportingLibs}}{$supportingLib}{conc}++;
					}
					
					# concordant is not bridging any BP
					else {}	
		
					last SUPPORTINGLIBS;
				
				}
	
			}
			
		}

	}
		
	close CONCFILE;	
	
	return($libraryStatistics);

}

### ---------------------------------------------------------------------------------

sub chrSize {

	my($chromosome, $ASSEMBLY, $libraryStatistics) = @_;

	$CHR_COORDS='';

	# Coordinates in chromosomes (automatically retrieved)
	@fetchChromSizes = `./fetchChromSizes $ASSEMBLY`;

	foreach my $fetchChromSizes (@fetchChromSizes) {

		if ($fetchChromSizes =~ /^(\S+)\t(\S+)/) {   # ^\|\s+(\S+)\s+\|\s+(\d+)\s+\|
	
			$CHR_COORDS->{$ASSEMBLY}->{$1} = $2;
					
		}

	}
	
	foreach my $library (keys %{$libraryStatistics}) {
	
		${$libraryStatistics}{$library}{$chromosome}{PEsPerchr} = ((${$libraryStatistics}{$library}{$chromosome}{conc}+${$libraryStatistics}{$library}{$chromosome}{disc})/$CHR_COORDS->{$ASSEMBLY}->{$chromosome});

	}	

	return($libraryStatistics);

}

### ---------------------------------------------------------------------------------

sub readRepeatsTrack {

	my($chr, $ASSEMBLY) = @_;
	
	open(REPCHROM, $REPEATFILES_PATH.$ASSEMBLY.'.'.$chr.'.repeats') or die ("ERROR: Could not read Repeats track for ASSEMBLY=$ASSEMBLY and chromosome $chr in REPEATFILES_PATH=$REPEATFILES_PATH folder. Please provide the track in a file named $ASSEMBLY.$chr.repeats in $REPEATFILES_PATH folder.\n\n");
	$repeats = <REPCHROM>;
	close REPCHROM;
	
}

### ---------------------------------------------------------------------------------

sub readSegdupsTrack {

	my($chr, $ASSEMBLY) = @_;
	
	open(REPCHROM, $REPEATFILES_PATH.$ASSEMBLY.'.'.$chr.'.segdups') or die ("ERROR: Could not read Segdups track for ASSEMBLY=$ASSEMBLY and chromosome $chr in REPEATFILES_PATH=$REPEATFILES_PATH folder. Please provide the track in a file named $ASSEMBLY.$chr.segdups in $REPEATFILES_PATH folder.\n\n");
	$segdups = <REPCHROM>;
	close REPCHROM;
	
}

### ---------------------------------------------------------------------------------

sub compute_alfa{

	$discord = 0; $total = 0;
	
	INVERSION: foreach $inv (@inversions) {
	
		SUPPORTINGLIBS: foreach $supportingLib (keys %{$inv->{supportingLibs}}) {
		
			next SUPPORTINGLIBS if (${$inv->{supportingLibs}}{$supportingLib}{conc} * ${$inv->{supportingLibs}}{$supportingLib}{disc} == 0);
			
			$discord += ${$inv->{supportingLibs}}{$supportingLib}{disc};
			$total +=  ${$inv->{supportingLibs}}{$supportingLib}{conc} + ${$inv->{supportingLibs}}{$supportingLib}{disc};
		}
	}
	
	if ($discord*$total == 0) {
	
		return(0.5);
		
	} else {
	
		return($discord/$total);
	}
	
}

### ---------------------------------------------------------------------------------

sub compute_likelihood{
	
	($invInfo) = @_; 
	
	SUPPORTINGLIBS: foreach $supportingLib (keys %{$invInfo->{supportingLibs}}) {

		if (${$invInfo->{supportingLibs}}{$supportingLib}{conc} + ${$invInfo->{supportingLibs}}{$supportingLib}{disc} == 0) {
			
			${$invInfo->{supportingLibs}}{$supportingLib}{likelihood} = 0;
			
		} else {
			
			${$invInfo->{supportingLibs}}{$supportingLib}{likelihood} = ($alfa**${$invInfo->{supportingLibs}}{$supportingLib}{disc})*((1-$alfa)**(${$invInfo->{supportingLibs}}{$supportingLib}{conc}));
		
		}
	}	 

}

### ---------------------------------------------------------------------------------

sub compute_score{

	($invInfo) = @_;
	
	my $T = $invInfo->{'concordantsBridgeBP1'} + $invInfo->{'concordantsBridgeBP2'} + $invInfo->{'posWeighted'} + $invInfo->{'negWeighted'};
	
	my $O = $invInfo->{'posWeighted'} + $invInfo->{'negWeighted'};
	
	my $E = '';
	
	if (($invInfo->{'concordantsBridgeBP1'} + $invInfo->{'concordantsBridgeBP2'}) == 0) {
	
		$E = $invInfo->{'posWeighted'} + $invInfo->{'negWeighted'};
		
	} else {
	
		$E = ($invInfo->{'concordantsBridgeBP1'} + $invInfo->{'concordantsBridgeBP2'} + $invInfo->{'posWeighted'} + $invInfo->{'negWeighted'})/2;
	
	}
	
	$invInfo->{score_ratio} = $E/$O;
	
	if ( $O != $T ) {
	
		$invInfo->{score_test} = (($O/$T)-($E/$T))/sqrt((($O/$T)*(1-($O/$T))/$T)+(($E/$T)*(1-($E/$T))/$T));
		
	} else {
	
		$invInfo->{score_test} = 1;
		
 	}
	 
}

### ---------------------------------------------------------------------------------

sub compute_theoreticalscores {

	($invInfo) = @_;
	
	my $lambda = 0;
	
	# Connect to UCSC if necessary
	if ($MAPABILITY_SEGDUPS eq 'yes') {
	
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
		
	#|# QUINES LIBRARIES SUPORTEN LA INVERSIÓ? LA LAMBDA S'HA DE CALCULAR NOMÉS EN FUNCIÓ D'AQUESTES LIBRARIES!!!
	
	#foreach $library (@{$invInfo->{supportingLibs}}) {
	
	SUPPORTINGLIBS: foreach $supportingLib (keys %{$invInfo->{supportingLibs}}) {
	
	# DISCORDANTS PART ---------------------------------------------------------
	# Calculate lambda (estimated # PEs at each BP)
	my $parcial_lambda = ${$libraryStatistics}{$supportingLib}{$invInfo->{chr}}{PEsPerchr} * min($DATA_STAT{mean}, $invInfo->{min_invSize});  
	#|# abans era: max_invSize
	
	$lambda += $parcial_lambda;

	}

	my $lambda_discHet = $lambda / 2;
	my $mapability_BP1=1;
	my $mapability_BP2=1;
		
	# Mapability correction
	if (($MAPABILITY_REPEATS eq 'yes') or ($MAPABILITY_SEGDUPS eq 'yes') or ($DISCARD_MAPPINGS_IN_REPEATS eq 'yes') or ($DISCARD_MAPPINGS_IN_SEGDUPS eq 'yes')) {
	
		($mapability_BP1) = calculateMapability($invInfo->{chr}, $invInfo->{bp1_start}, $invInfo->{bp1_end}, $DATA_STAT{mean}, $invInfo->{bp2_start}, $invInfo->{bp2_end});
		
		($mapability_BP2) = calculateMapability($invInfo->{chr}, $invInfo->{bp2_start}, $invInfo->{bp2_end}, $DATA_STAT{mean}, $invInfo->{bp1_start}, $invInfo->{bp1_end});
		
	} 

	# Correct lambda at each BP
	my $lambda_discHet_correctedBP1 = $lambda_discHet * $mapability_BP1;
	my $lambda_discHet_correctedBP2 = $lambda_discHet * $mapability_BP2;
	
	# Calculate probability at each BP
	my $probDisc_BP1 = ppois($invInfo->{posWeighted}, $lambda_discHet_correctedBP1);
	my $probDisc_BP2 = ppois($invInfo->{negWeighted}, $lambda_discHet_correctedBP2);
	my $probDisc_all = ppois(($invInfo->{posWeighted}+$invInfo->{negWeighted}), ($lambda_discHet_correctedBP1+$lambda_discHet_correctedBP2));	
	
	$invInfo->{thscore_lambda} = $lambda_discHet;
	$invInfo->{thscore_lambda_BP1} = $lambda_discHet_correctedBP1;
	$invInfo->{thscore_lambda_BP2} = $lambda_discHet_correctedBP2;
	$invInfo->{thscore_mapability_BP1} = $mapability_BP1;
	$invInfo->{thscore_mapability_BP2} = $mapability_BP2;
	$invInfo->{thscore_BP1} = $probDisc_BP1;
	$invInfo->{thscore_BP2} = $probDisc_BP2;
	$invInfo->{thscore_all} = $probDisc_all;
	
}

### ---------------------------------------------------------------------------------

sub calculateMapability {

	my($chromosome, $start, $end, $libSize, $otherStart, $otherEnd) = @_;

	my $region_coordStart = int($start - $libSize + 0.5);
	my $region_coordEnd = int($end + $libSize + 0.5);
	my $BPinterval = new Number::Interval( Min => $region_coordStart, Max => $region_coordEnd, IncMin => 1, IncMax => 1);
	
	my $region_otherStart = int($otherStart - $libSize + 0.5);
	my $region_otherEnd = int($otherEnd + $libSize + 0.5);
	
	my $region_length = int(($end - $start + 1) + 2*$libSize + 0.5);
	
	# Define n & x
	my $n = $MAPABILITY_FRAGMENT_SIZE;
	my $x = $MAPABILITY_NUM_CHANGES;	
	
	my $w_segdups = 1;
	my $w_reps = 1;
	
	my $track_for_counting_segdups_length = 0 x $region_length;
	
	if (($MAPABILITY_SEGDUPS eq 'yes') and ($DISCARD_MAPPINGS_IN_SEGDUPS eq 'no')) {
	
		# Find inverted segmental duplications and do related calculations
		# Find overlapping segdups in the database and calculate avg percentage identity
		$select = "SELECT DISTINCTROW chromStart, chromEnd, fracMatchIndel
				FROM genomicSuperDups WHERE chrom = '$chromosome' AND (
					(chromStart >= $region_coordStart AND chromStart <= $region_coordEnd) OR
					(chromEnd >= $region_coordStart AND chromEnd <= $region_coordEnd) OR
					(chromStart < $region_coordStart AND chromEnd > $region_coordEnd))
					AND strand = '-' AND otherChrom = '$chromosome' AND (
					(otherStart >= $region_otherStart AND otherStart <= $region_otherEnd) OR
					(otherEnd >= $region_otherStart AND otherEnd <= $region_otherEnd) OR
					(otherStart < $region_otherStart AND otherEnd > $region_otherEnd))
				ORDER BY fracMatchIndel ASC"; 
		$dbh->{LongReadLen} = 64000;
		$dbh->{LongTruncOk} = 1;
		$sth_segdups = $dbh->prepare($select);
		$sth_segdups->execute();
	
		$w_segdups_ponderada = 0;
		$length_per_w_segdups_ponderada = 0;
	
		while ($result = $sth_segdups->fetchrow_hashref()) {
	
			# Calcular troç que afecta...
			# Tinc les coordenades del BP: $region_coordStart - $region_coordEnd
			# Tinc les coordenades de la/les SegDup/s: chromStart - chromEnd
			$SEGDUPinterval = new Number::Interval( Min => $result->{chromStart}, 
								Max => $result->{chromEnd}, 
								IncMin => 1, IncMax => 1 );
							
			# NOU!: Calcular el track que divideix entre CORE i EXTREMS
			$length_SEGDUP_this = $result->{chromEnd} - $result->{chromStart} + 1;
			$length_1EXTREME = int($length_SEGDUP_this*((1-$MAPABILITY_SEGDUPS_CORE)/2) + 0.5);
			$length_CORE = $length_SEGDUP_this - 2*$length_1EXTREME;
			$newTrack_SEGDUP_COREEXTREMES = ('E' x $length_1EXTREME) . ('C' x $length_CORE) . ('E' x $length_1EXTREME);

			# Intersect the two intervals
			$i = $BPinterval->copy;
			$status = $i->intersection( $SEGDUPinterval );
			%interval = $i->minmax_hash;
		
			next if ($status == 0);  # tot i que no hauria de passar mai...
		
			# Length of SEGDUP within BP
			$length_of_SEGDUP_within_BP = $interval{max} - $interval{min} + 1;
		
			# NOU!: substract fragment of new track (CORE/EXTREMES) which is inside Breakpoint
			$newTrack_SEGDUP_COREEXTREMES_part = substr $newTrack_SEGDUP_COREEXTREMES, ($interval{min}-$result->{chromStart}), $length_of_SEGDUP_within_BP;
		
			# Update track for segdups
			$min_forTrack = $interval{min} - $region_coordStart;
			substr $track_for_counting_segdups_length, $min_forTrack, $length_of_SEGDUP_within_BP, $newTrack_SEGDUP_COREEXTREMES_part;
		
			# NOU!: Weigh identity
			#---------------------
			# Calculate Id in CORE & EXTREMES
			$id_CORE = (1-(((1-$result->{fracMatchIndel})*$length_SEGDUP_this*$MAPABILITY_SEGDUPS_CHANGES_CORE)/($MAPABILITY_SEGDUPS_CORE*$length_SEGDUP_this)));
			$id_EXTREMES = (1-(((1-$result->{fracMatchIndel})*$length_SEGDUP_this*$MAPABILITY_SEGDUPS_CHANGES_CORE)/((1-$MAPABILITY_SEGDUPS_CORE)*$length_SEGDUP_this)));
		
			# Calculate mapability in CORE & EXTREMES
			$pbinom_CORE = pbinom($x, $n, 1-$id_CORE);
			$w_segdups_CORE = 1 - $pbinom_CORE;
		
			$pbinom_EXTREMES = pbinom($x, $n, 1-$id_EXTREMES);
			$w_segdups_EXTREMES = 1 - $pbinom_EXTREMES;
				
			# Calculate Length of EXTREMES
			$length_SEGDUP_EXTREMES_fromTrack = 0;
			while ($newTrack_SEGDUP_COREEXTREMES_part =~ /E/g) {
				$length_SEGDUP_EXTREMES_fromTrack++;
			}
		
			# Calculate Length of CORE
			$length_SEGDUP_CORE_fromTrack = 0;
			while ($newTrack_SEGDUP_COREEXTREMES_part =~ /C/g) {
				$length_SEGDUP_CORE_fromTrack++;
			}
		
			# Ponderate Mapability
			$w_segdups_ponderada += (($length_SEGDUP_EXTREMES_fromTrack*$w_segdups_EXTREMES)+($length_SEGDUP_CORE_fromTrack*$w_segdups_CORE));
			$length_per_w_segdups_ponderada += ($length_SEGDUP_EXTREMES_fromTrack+$length_SEGDUP_CORE_fromTrack);

			# La identitat més alta
			$p = $result->{fracMatchIndel};
		
		}
	
		$sth_segdups->finish();	
	
		# Finish weighting identity
		if ($length_per_w_segdups_ponderada > 0) {
			$w_segdups_ponderada /= $length_per_w_segdups_ponderada;
		}
	
		$w_segdups = $w_segdups_ponderada;

	}	

	#############################
	# Do calculations for repeats
	
	if ($MAPABILITY_REPEATS eq 'yes') {
	
		# Fix an avg percentage identity for repeats
		$p = $MAPABILITY_REPEATS_IDENTITY;

		# Calculate binomial prob $pbinom given $x, $n and $p
		$pbinom = pbinom($x, $n, 1-$p);

		# Calculate mapability of repetitive bases as $w = 1 - $pbinom
		$w_reps = 1 - $pbinom;

	}
	
	#############################
	# Calculate lengths for mapability
	my $partTrack_segdups; my $partTrack_repeats;
	if ($DISCARD_MAPPINGS_IN_SEGDUPS eq 'yes') {
	
		$partTrack_segdups = substr($segdups, $region_coordStart, $region_length);
	
	}
	if (($DISCARD_MAPPINGS_IN_REPEATS eq 'yes') or ($MAPABILITY_REPEATS eq 'yes')) {
	
		$partTrack_repeats = substr($repeats, $region_coordStart, $region_length);
	
	}
	
	my($count_norep, $count_rep, $count_segdups, $count_discarded) = countRepeats($partTrack_repeats, $partTrack_segdups, $track_for_counting_segdups_length);

	my $mapability = ((1*$count_norep)+($w_reps*$count_rep)+($w_segdups*$count_segdups)+(0*$count_discarded))/$region_length;
	
	return($mapability);

}

### ---------------------------------------------------------------------------------

sub countRepeats {

	my($portion_repeats, $portion_segdups, $portion_segdups_new) = @_;	# ¡¡¡ be aware that $portion_repeats OR 
																		#     $portion_segdups might be NULL !!!
	# Which repeats?
	my %repCodes = ();
	if (($DISCARD_MAPPINGS_IN_REPEATS eq 'yes') and ($DISCARD_REPEATS_LIST ne 'all')) {
	
		my @repCodes = split //, $DISCARD_REPEATS_LIST;
		$repCodes{$_}++ for (@repCodes);
	
	}
	
	# Initialize variables							     
	my $count_norep=0; my $count_rep=0; my $count_segdups=0; my $count_discarded=0; 

	# Start counting...
	for (my $i=0; $i<length($portion_segdups_new); $i++) {
	
		# Read the base in the tracks
		$sduBase_new = substr($portion_segdups_new, $i, 1);   # this track always exist, although might be all 0s
		
		if (($MAPABILITY_REPEATS eq 'yes') or ($DISCARD_MAPPINGS_IN_REPEATS eq 'yes')) {
			$repBase = substr($portion_repeats, $i, 1);
		} else {
			$repBase = '-';			      	# in case MAPABILITY_REPEATS=no & DISCARD_MAPPINGS_IN_REPEATS=no, 
											# no problem because $repBase=-
		}

		if ($DISCARD_MAPPINGS_IN_SEGDUPS eq 'yes') {
			$sduBase = substr($portion_segdups, $i, 1);
		} else {
			$sduBase = 0;			      	# in case DISCARD_MAPPINGS_IN_SEGDUPS=no, 
											# no problem because $sduBase=0
		}
		
		# Count base in corresponding category
		# 1- count_discarded
		# 2- count_segdups
		# 3- count_rep
		# 4- count_norep
		
		if (($DISCARD_MAPPINGS_IN_SEGDUPS eq 'yes') and ($sduBase ne '0')) {
		
			$count_discarded++;
		
		} elsif (($DISCARD_MAPPINGS_IN_REPEATS eq 'yes') and ($repBase ne '-') and (
			($DISCARD_REPEATS_LIST eq 'all') or (exists $repCodes{$repBase}))) {
		
			$count_discarded++;
		
		} elsif (($MAPABILITY_SEGDUPS eq 'yes') and ($sduBase_new ne '0')) {
		
			$count_segdups++;
		
		} elsif (($MAPABILITY_REPEATS eq 'yes') and ($repBase ne '-')) {
		
			$count_rep++;
		
		} else {
		
			$count_norep++;
		
		}
		
	}
	
	return($count_norep, $count_rep, $count_segdups, $count_discarded);

}

### ---------------------------------------------------------------------------------

sub printFile {

	my($invInfo) = @_;    
	
	my $printVariable = $invInfo->{'posId'}."\t".$invInfo->{'negId'}."\t".$invInfo->{'chr'}."\t".
		($invInfo->{'posWeighted'}+$invInfo->{'negWeighted'})."\t".
		$invInfo->{'bp1_start'}."\t".$invInfo->{'bp1_end'}."\t".$invInfo->{'bp2_start'}."\t".$invInfo->{'bp2_end'}."\t".
		$invInfo->{'posWeighted'}."(".$invInfo->{'posSupport'}.")/".
		$invInfo->{'negWeighted'}."(".$invInfo->{'negSupport'}.")\t".
		($invInfo->{'concordantsBridgeBP1'}+$invInfo->{'concordantsBridgeBP2'})."\t".$invInfo->{'score_ratio'}."\t".
		$invInfo->{'score_test'}."\t".$invInfo->{'thscore_lambda'}."\t".
		$invInfo->{'thscore_mapability_BP1'}."\t".$invInfo->{'thscore_mapability_BP2'}."\t".
		$invInfo->{'thscore_lambda_BP1'}."\t".$invInfo->{'thscore_lambda_BP2'}."\t".
		$invInfo->{'thscore_BP1'}."\t".$invInfo->{'thscore_BP2'}."\t".$invInfo->{'thscore_all'}."\n";
		
	my @supportingLibs_sorted = sort(keys %{$invInfo->{supportingLibs}});
	SUPPORTINGLIBS: foreach $supportingLib (@supportingLibs_sorted) {
	
		$printVariable .= ">\t".$supportingLib."\t".${$invInfo->{supportingLibs}}{$supportingLib}{conc}."\t".${$invInfo->{supportingLibs}}{$supportingLib}{disc}."\t".${$invInfo->{supportingLibs}}{$supportingLib}{likelihood}."\n";   # ."\t".${$invInfo->{supportingLibs}}{$supportingLib}{genotype}
	
	}	
	
	$printVariable .= ("-" x 100) . "\n";
		
	return($printVariable);

}
