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
# Name:      	GRIAL.pl 
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
#     Purpose......: predict inversions from paired-end mapping data
#
# Changes   Date(DD.MM.YYYY)   NAME   DESCRIPTION
#
#------------------------------------------------------------------------------------

use Statistics::Descriptive;
use Cwd;

print localtime( ) . " ... started\n";

### READ CONFIG FILE ----------------------------------------------------------------
read_config_file($ARGV[0]);

print localtime( ) . " ... finished reading config file\n";

### SET STATISTICS OF THE CONCORDANT PEMs -------------------------------------------
concordant_statistics();

print localtime( ) . " ... finished setting parameters\n";

### Read the DISCORDANTS file -------------------------------------------------------
load_data();

print localtime( ) . " ... finished loading data\n";

### CLUSTERING & INVERSION PREDICTION stages ----------------------------------------
# Fork chromosomes
my @childs_fork1 = ();

foreach my $chromosome (@CHROMOSOMES) {

	my $pid_fork1 = fork();
	
	if ($pid_fork1) {
		
		# parent
		push(@childs_fork1, $pid_fork1);
		
	} elsif ($pid_fork1 == 0) { 
	
		# Sort all + and - mappings by coordinate --> before forking by strand!!!!!!!!!!! (only way to get correct indexes after clustering)
		$ESP_INFO{$chromosome.'+'} =  [ sort { get_extreme($a) <=> get_extreme($b) } @{ $ESP_INFO{$chromosome.'+'} } ] ;
		$ESP_INFO{$chromosome.'-'} =  [ sort { get_extreme($b) <=> get_extreme($a) } @{ $ESP_INFO{$chromosome.'-'} } ] ;	

		# Fork strand
		my @childs_fork2 = ();
	
		foreach my $strand ('+', '-') {

			my $pid_fork2 = fork();
		
			if ($pid_fork2) {
		
				# parent
				push(@childs_fork2, $pid_fork2);
			
			} elsif ($pid_fork2 == 0) {

				# Open output file(s)
				openfiles_clustering($chromosome.$strand);
				
				# Clustering stage
				clustering($chromosome.$strand);
				
				# Close output file(s)
				closefiles_clustering($chromosome.$strand);
				
				exit(0);
			
			} else {
		
				die "couldn’t fork strands for $chromosome: $!\n";
			
			}

		}
	
		# Wait results for both strands to finish!
		foreach (@childs_fork2) {
	
			waitpid($_, 0);
		
		}	

		# Start $SUMMARY_RESULTS variable
		$SUMMARY_RESULTS;
		
		# Open output file(s)
		openfiles_inversions($chromosome);
	
		# Load clustering data into array
		load_clusters($chromosome);

		# Inversion prediction stage
		predict_inversions($chromosome);
		# Close output file(s)
		closefiles_inversions($chromosome);
		
		# Write summary results
		writesummaryresults($chromosome);
		
		exit(0);
			
	} else {

		die "couldn’t fork $chromosome: $!\n";
	
	}

}

# Wait results for all chromosomes to finish!
foreach (@childs_fork1) {

	waitpid($_, 0);

}

print localtime( ) . " ... finished clustering & inversion prediction on all chromosomes\n";

### WRITE SUMMARY RESULTS -----------------------------------------------------------
close SUMMARYRESULTS;

print localtime( ) . " ... finished writing summary results\n";

print "Done!!!\n\n";

### SCORING -------------------------------------------------------------------------

if ($SCORE_INVERSIONS eq 'yes') {

print "GRIAL PREDICTIONS ARE FINISHED. NOW YOU ARE REDIRECTED TO GRIALscore.pl >>>>>>\n\n";

system("perl ./GRIALscore.pl ".$ARGV[0]);

}

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
	print "\n";

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
		
	if ($SCORE_INVERSIONS !~ /^(yes|no)$/) {
	
		if (-e $CONCORDANTS_FILENAME_PREFIX) {
			$SCORE_INVERSIONS = 'yes';
			print "WARNING: SCORE_INVERSIONS was not provided or is invalid -> set to yes\n";
		} else {
			$SCORE_INVERSIONS = 'no';
			print "WARNING: SCORE_INVERSIONS was not provided or is invalid -> set to no\n";
		}	
		
	}
	
	if ($OVERLAPPINGENDS_PERC !~ /^\d+\.?\d*$/) {
		$OVERLAPPINGENDS_PERC = 50;
		print "WARNING: Percentage to consider overlapping ends (OVERLAPPINGENDS_PERC) was not provided or is invalid -> set to default (50%)\n";
	}

	if ($DUPLICATEPEMS_BP !~ /^\d+$/) {
		$DUPLICATEPEMS_BP = 5;
		print "WARNING: Maximum distance to consider artifactually duplicated reads (DUPLICATEPEMS_BP) was not provided or is invalid -> set to default (5bp)\n";
	}

	if ($MIN_SUPPORT_CLUSTERS !~ /^\d+$/) {
		$MIN_SUPPORT_CLUSTERS = 1;
		print "WARNING: MIN_SUPPORT_CLUSTERS was not provided or is invalid -> set to default (1 PEM per valid cluster)\n";
	}

	if ($MIN_SUPPORT_INVERSIONS !~ /^\d+$/) {
		$MIN_SUPPORT_INVERSIONS = 2;
		print "WARNING: MIN_SUPPORT_INVERSIONS was not provided or is invalid -> set to default (2 PEMs in total per valid inversion)\n";
	}
	
	if (-d $OUTPUT_PATH){
		if ($OUTPUT_PATH !~ /\/$/) {
			$OUTPUT_PATH .= '/';
		}
	} else {
		$OUTPUT_PATH = '';
	    	print "WARNING: OUTPUT_PATH was invalid -> set to current working path (".getcwd().")\n";
	}	
	
	if ($BP_DEFINITION !~ /^(wide|narrow|all)$/) {
		$BP_DEFINITION = 'all';
		print "WARNING: BP_DEFINITION was not provided or is invalid -> set to default (all)\n";
	}
	
	unless (($CHROMOSOMES eq 'all') or ($CHROMOSOMES eq '')) {
		@CHROMOSOMES = split /[,\s]+/, $CHROMOSOMES;
		%CHROMOSOMES = map { $_ => undef } @CHROMOSOMES;
		print "These chromosomes will be analyzed: ".(join(', ', @CHROMOSOMES))."\n";
	}
	
	unless (($LIBRARIES eq 'all') or ($LIBRARIES eq '')) {
		@LIBRARIES = split /[,\s]+/, $LIBRARIES;
		%LIBRARIES = map { $_ => undef } @LIBRARIES;
		print "These libraries will be analyzed: ".(join(', ', @LIBRARIES))."\n";
	}
	
	if ($ASSEMBLY eq '') {
		$ASSEMBLY = 'no';
		print "WARNING: ASSEMBLY was not provided or is invalid -> set to default (no)\n";
	}
	
	if ($OUTPUT_PEM_INDEXES !~ /^(yes|no)$/) {
		$OUTPUT_PEM_INDEXES = 'no';
		print "WARNING: OUTPUT_PEM_INDEXES was not provided or is invalid -> set to default (no)\n";
	}
	
	if ($OUTPUT_PEM_INDEXES !~ /^(yes|no)$/) {
		$OUTPUT_PEM_INDEXES = 'no';
		print "WARNING: OUTPUT_PEM_INDEXES was not provided or is invalid -> set to default (no)\n";
	}
	
	if ($ALLOW_SINGLE_CLUSTER_INVERSIONS !~ /^(yes|no)$/) {
		$ALLOW_SINGLE_CLUSTER_INVERSIONS = 'yes';
		print "WARNING: ALLOW_SINGLE_CLUSTER_INVERSIONS was not provided or is invalid -> set to default (yes)\n";
	}
	
	if ($JOINPOSNEGCLUSTERS_STRICT !~ /^(yes|no)$/) {
		$JOINPOSNEGCLUSTERS_STRICT = 'yes';
		print "WARNING: JOINPOSNEGCLUSTERS_STRICT was not provided or is invalid -> set to default (yes)\n";
	}
	
	if ($MAX_ALLOWED_MAPPINGS !~ /^(\d+|any)$/) {
		$MAX_ALLOWED_MAPPINGS = 10;
		print "WARNING: MAX_ALLOWED_MAPPINGS was not provided or is invalid -> set to default (10)\n";
	}
	
	if ($DISCARD_MAPPINGS_IN_REPEATS !~ /^(no|\d+)$/) {
		$DISCARD_MAPPINGS_IN_REPEATS = 'no';
		print "WARNING: DISCARD_MAPPINGS_IN_REPEATS was not provided or is invalid -> set to default (no)\n";
	}
	
	if ($DISCARD_MAPPINGS_IN_SEGDUPS !~ /^(no|\d+)$/) {
		$DISCARD_MAPPINGS_IN_SEGDUPS = 'no';
		print "WARNING: DISCARD_MAPPINGS_IN_SEGDUPS was not provided or is invalid -> set to default (no)\n";
	}
	
	if ($TAG_MAPPINGS_IN_REPEATS !~ /^(no|\d+)$/) {
		$TAG_MAPPINGS_IN_REPEATS = 'no';
		print "WARNING: TAG_MAPPINGS_IN_REPEATS was not provided or is invalid -> set to default (no)\n";
	}
	
	if ($TAG_MAPPINGS_IN_SEGDUPS !~ /^(no|\d+)$/) {
		$TAG_MAPPINGS_IN_SEGDUPS = 'no';
		print "WARNING: TAG_MAPPINGS_IN_SEGDUPS was not provided or is invalid -> set to default (no)\n";
	}
	
	if (($DISCARD_MAPPINGS_IN_REPEATS ne 'no') or ($TAG_MAPPINGS_IN_REPEATS ne 'no') or ($DISCARD_MAPPINGS_IN_SEGDUPS ne 'no') or ($TAG_MAPPINGS_IN_SEGDUPS ne 'no')) {
	
		if (-d $REPEATFILES_PATH){
			if ($REPEATFILES_PATH !~ /\/$/) {
				$REPEATFILES_PATH .= '/';
			}
		} else {
			$REPEATFILES_PATH = getcwd().'/repeatfiles/';
				print "WARNING: REPEATFILES_PATH was invalid -> set to default (".getcwd().'/repeatfiles/'.")\n";
		}		
	
		if ($ASSEMBLY eq 'no') {
			die("ERROR: Please provide a valid ASSEMBLY or choose 'no' for all discard/tag repeats/segdups parameters (DISCARD_MAPPINGS_IN_REPEATS, DISCARD_MAPPINGS_IN_SEGDUPS, TAG_MAPPINGS_IN_REPEATS, TAG_MAPPINGS_IN_SEGDUPS)!\n\n");
		}
		
		if ($DISCARD_MAPPINGS_IN_REPEATS ne 'no') {
	
			if ($DISCARD_REPEATS_LIST eq 'all') {
				$DISCARD_REPEATS_LIST = 'SLTDIXUROECdstulo';
			} elsif ($DISCARD_REPEATS_LIST !~ /^[A-Za-z]+$/) {
				$DISCARD_REPEATS_LIST = 'SLTDIXUROECdstulo';
				print "WARNING: DISCARD_REPEATS_LIST was not provided or is invalid -> set to default (all)\n";	
			} else {}
	
			if ($DISCARD_REPEATS_NUMREADS !~ /^(any|both)$/) {
				$DISCARD_REPEATS_NUMREADS = 'any';
				print "WARNING: DISCARD_REPEATS_NUMREADS was not provided or is invalid -> set to default (any)\n";
			}
	
		}
		
		if ($TAG_MAPPINGS_IN_REPEATS ne 'no') {
		
			if ($TAG_REPEATS_LIST eq 'all') {
				$TAG_REPEATS_LIST = 'SLTDIXUROECdstulo';
			} elsif ($TAG_REPEATS_LIST !~ /^[A-Za-z]+$/) {
				$TAG_REPEATS_LIST = 'SLTDIXUROECdstulo';
				print "WARNING: TAG_REPEATS_LIST was not provided or is invalid -> set to default (all)\n";	
			} else {}
			
			if ($TAG_REPEATS_NUMREADS !~ /^(any|both)$/) {
				$TAG_REPEATS_NUMREADS = 'any';
				print "WARNING: TAG_REPEATS_NUMREADS was not provided or is invalid -> set to default (any)\n";
			}
			
			if ($TAG_MAPPINGS_IN_REPEATS_ALL !~ /^(yes|no)$/) {
				$TAG_MAPPINGS_IN_REPEATS_ALL = 'yes';
				print "WARNING: TAG_MAPPINGS_IN_REPEATS_ALL was not provided or is invalid -> set to default (yes)\n";
			}
			
		}
		
		if ($DISCARD_MAPPINGS_IN_SEGDUPS ne 'no') {
		
			if ($DISCARD_SEGDUPS_NUMREADS !~ /^(any|both)$/) {
				$DISCARD_SEGDUPS_NUMREADS = 'any';
				print "WARNING: DISCARD_SEGDUPS_NUMREADS was not provided or is invalid -> set to default (any)\n";
			}
	
		}
	
		if ($TAG_MAPPINGS_IN_SEGDUPS ne 'no') {
		
			if ($TAG_SEGDUPS_NUMREADS !~ /^(any|both)$/) {
				$TAG_SEGDUPS_NUMREADS = 'any';
				print "WARNING: TAG_SEGDUPS_NUMREADS was not provided or is invalid -> set to default (any)\n";
			}
			
			if ($TAG_MAPPINGS_IN_SEGDUPS_ALL !~ /^(yes|no)$/) {
				$TAG_MAPPINGS_IN_SEGDUPS_ALL = 'yes';
				print "WARNING: TAG_SEGDUPS_IN_REPEATS_ALL was not provided or is invalid -> set to default (yes)\n";
			}
	
		}
		
	}	
	
	if ($TAG_INCONGRUENT_BPS !~ /^(yes|no)$/) {
		$TAG_INCONGRUENT_BPS = 'yes';
		print "WARNING: TAG_INCONGRUENT_BPS was not provided or is invalid -> set to default (yes)\n";
	}
	
	if ($TAG_MULTIPLE_USE_OF_CLUSTERS !~ /^(yes|no)$/) {
		$TAG_MULTIPLE_USE_OF_CLUSTERS = 'no';
		print "WARNING: TAG_MULTIPLE_USE_OF_CLUSTERS was not provided or is invalid -> set to default (no)\n";
	}
	
	if ($REPAIR_BP_INCONSISTENCES !~ /^(yes|no)$/) {
		$REPAIR_BP_INCONSISTENCES = 'no';
		print "WARNING: REPAIR_BP_INCONSISTENCES was not provided or is invalid -> set to default (no)\n";
	}
	
	if ($REPAIR_BP_INCONSISTENCES eq "yes") {
	
		$CHR_COORDS='';
	
		# Coordinates in chromosomes (automatically retrieved)
		@fetchChromSizes = `./fetchChromSizes $ASSEMBLY`;

		foreach my $fetchChromSizes (@fetchChromSizes) {

			if ($fetchChromSizes =~ /^(\S+)\t(\S+)/) {   # ^\|\s+(\S+)\s+\|\s+(\d+)\s+\|
		
				$CHR_COORDS->{$ASSEMBLY}->{$1} = $2;
						
			}
	
		}
	
	}	
	
	if ($ANALYZE_RANDOM_CHROMOSOMES !~ /^(yes|no)$/) {
		$ANALYZE_RANDOM_CHROMOSOMES = 'no';
		print "WARNING: ANALYZE_RANDOM_CHROMOSOMES was not provided or is invalid -> set to default (no)\n";
	}
	
	if ($WEIGHT_PEM_SUPPORT !~ /^(yes|no)$/) {
		$WEIGHT_PEM_SUPPORT = 'no';
		print "WARNING: WEIGHT_PEM_SUPPORT was not provided or is invalid -> set to default (yes)\n";
	}
	

	# Summary results -> only variable to which all childs will write!!!
	open(SUMMARYRESULTS, '>>'.$OUTPUT_PATH.$OUTPUT_PREFIX.'summaryresults.out');
#my# added >> instead of > above; commented header line below
#my#	print SUMMARYRESULTS "chromosome	PEMs (+/-)	clusters (+/-)	!clusters (+/-)	inversions(both/+/-)	!inversions(A/B/C/D/E/F)\n";
	
}

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

sub fragment_lenght {

	my($esp) = @_;
	if ($esp =~ /^(chr\S+)\s+(\d+)\s+(\d+)\s+.+\s+\S+/){
		return $3-$2+1;
	}
	
}

sub load_data {

	open(DATA_FILE, $DISCORDANTS_FILENAME) or die ("\nERROR: Could not read discordants file!\n\n");

	%ESP_INFO = (); my $numESPsloaded = 0; my %temp_chromosomes_seen = (); my %temp_repeatTracks = (); my %temp_segdupTracks = (); my $solapantes = 0;

	while ( my $line = <DATA_FILE> ) {		

		next unless $line =~ /^(\S+)\t(\S+)\t(chr\S+)\t(\d+)\t(\d+)\t(\d+)\t(\d+)\t(\+|\-)\t(R|F)\t?(\d*)/;

		# Filter out PEMs derived from too many mappings
		next if (($MAX_ALLOWED_MAPPINGS ne 'any') and ($10 > $MAX_ALLOWED_MAPPINGS));

		# Filter out PEMs from random chromosomes
		next if (($ANALYZE_RANDOM_CHROMOSOMES eq 'no') and ($3 =~ /random/));

		# Filter out PEMs from chromosomes not in the chromosomes-to-analyze list
		if (scalar(@CHROMOSOMES) > 0) {
			next unless (exists $CHROMOSOMES{$3});
		}
	
		# Filter out PEMs from libraries not in the libraries-to-analyze list
		if (scalar(@LIBRARIES) > 0) {
			next unless (exists $LIBRARIES{$1});
		}
		
		# Filter out PEMs with overlapped ends and other inconsistences        #@# somewhere here duplicated names should be masked...
		if ((set_overlap($4, $5, $6, $7) == 0) and ($5>$4) and ($7>$6) and ($6>$4)) {
		
			# Filter out / Tag mappings in repeats or segdups
			my $temp_tags = '';
			if (($DISCARD_MAPPINGS_IN_REPEATS ne 'no') or ($TAG_MAPPINGS_IN_REPEATS ne 'no')) {
			
				# Open repeats file for current chromosome
				unless (exists $temp_repeatTracks{$3}) {
					open(REPCHROM, $REPEATFILES_PATH.$ASSEMBLY.'.'.$3.'.repeats') or die ("ERROR: Repeats file for $ASSEMLY/$3 was not found! Please provide a valid file ($ASSEMBLY.$3.repeats) in $REPEATFILES_PATH or choose 'no' for all discard/tag repeats parameters (DISCARD_MAPPINGS_IN_REPEATS, TAG_MAPPINGS_IN_REPEATS)!\n\n");
					$temp_repeatTracks{$3} = <REPCHROM>;
					close REPCHROM;
				}
			
				# Substr track part for current coordinates, check repeats content in left and right reads
				# Left
				my($temp_repeatsleftDiscard, $temp_repeatsleftTag) = overlapRepeats(substr($temp_repeatTracks{$3}, $4-1, $5-$4+1));
				
				# Right
				my($temp_repeatsrightDiscard, $temp_repeatsrightTag) = overlapRepeats(substr($temp_repeatTracks{$3}, $6-1, $7-$6+1));
				
				
				if (($DISCARD_MAPPINGS_IN_REPEATS ne 'no') and ($DISCARD_REPEATS_NUMREADS eq 'both') and ($temp_repeatsleftDiscard eq 'yes') and ($temp_repeatsrightDiscard eq 'yes')) {
					next;
				
				} elsif (($DISCARD_MAPPINGS_IN_REPEATS ne 'no') and ($DISCARD_REPEATS_NUMREADS eq 'any') and (($temp_repeatsleftDiscard eq 'yes') or ($temp_repeatsrightDiscard eq 'yes'))) {
					next;
				
				} else {
				
					if (($TAG_MAPPINGS_IN_REPEATS ne 'no') and ($TAG_REPEATS_NUMREADS eq 'both') and ($temp_repeatsleftTag eq 'yes') and ($temp_repeatsrightTag eq 'yes')) {
						$temp_tags .= '^';
				
					} elsif (($TAG_MAPPINGS_IN_REPEATS ne 'no') and ($TAG_REPEATS_NUMREADS eq 'any') and (($temp_repeatsleftTag eq 'yes') or ($temp_repeatsrightTag eq 'yes'))) {
						$temp_tags .= '^';
				
					} else {}
				
				}
				
			}
				
			if (($DISCARD_MAPPINGS_IN_SEGDUPS ne 'no') or ($TAG_MAPPINGS_IN_SEGDUPS ne 'no')) {
			
				# Open repeats file for current chromosome
				unless (exists $temp_segdupTracks{$3}) {
					open(REPCHROM, $REPEATFILES_PATH.$ASSEMBLY.'.'.$3.'.segdups') or die ("ERROR: Segdups file for $ASSEMLY/$3 was not found! Please provide a valid file ($ASSEMBLY.$3.segdups) in $REPEATFILES_PATH or choose 'no' for all discard/tag segdups parameters (DISCARD_MAPPINGS_IN_SEGDUPS, TAG_MAPPINGS_IN_SEGDUPS)!\n\n");
					$temp_segdupTracks{$3} = <REPCHROM>;
					close REPCHROM;
				}
				
				# Substr track part for current coordinates, check segdups content in left and right reads
				# Left
				my($temp_segdupsleftDiscard, $temp_segdupsleftTag) = overlapSegdups(substr($temp_segdupTracks{$3}, $4-1, $5-$4+1));
				
				# Right
				my($temp_segdupsrightDiscard, $temp_segdupsrightTag) = overlapSegdups(substr($temp_segdupTracks{$3}, $6-1, $7-$6+1));
				
				
				if (($DISCARD_MAPPINGS_IN_SEGDUPS ne 'no') and ($DISCARD_SEGDUPS_NUMREADS eq 'both') and ($temp_segdupsleftDiscard eq 'yes') and ($temp_segdupsrightDiscard eq 'yes')) {
					next;
				
				} elsif (($DISCARD_MAPPINGS_IN_SEGDUPS ne 'no') and ($DISCARD_SEGDUPS_NUMREADS eq 'any') and (($temp_segdupsleftDiscard eq 'yes') or ($temp_segdupsrightDiscard eq 'yes'))) {
					next;
				
				} else {
				
					if (($TAG_MAPPINGS_IN_SEGDUPS ne 'no') and ($TAG_SEGDUPS_NUMREADS eq 'both') and ($temp_segdupsleftTag eq 'yes') and ($temp_segdupsrightTag eq 'yes')) {
						$temp_tags .= ':';
				
					} elsif (($TAG_MAPPINGS_IN_SEGDUPS ne 'no') and ($TAG_SEGDUPS_NUMREADS eq 'any') and (($temp_segdupsleftTag eq 'yes') or ($temp_segdupsrightTag eq 'yes'))) {
						$temp_tags .= ':';
				
					} else {}
				
				}
				
			}
			
#v15#			$temp_tags =~ s/(.)(?=.*?\1)//g;
			
			my $temp_maps;
			
			if ($10 < 1) {
				$temp_maps = 1;
			} else {
				$temp_maps = $10;
			}
			
			push (@{ $ESP_INFO{$3.$8} }, [ $1, $2, $3, $4, $5, $6, $7, $8, $9, set_sumval($4, $5, $6, $7, $8), undef, 0, $temp_maps, undef, $temp_tags ]); 
			$numESPsloaded++;
			$temp_chromosomes_seen{$3} = undef;

		} else {
		
			$solapantes++;
			
		} 
	}
	
	close DATA_FILE;
	
	if ($numESPsloaded > 0) {
		print "$numESPsloaded ESPs loaded. $solapantes ESPs with overlapping ends removed\n";
		unless (scalar(@CHROMOSOMES) > 0) {
			@CHROMOSOMES = keys %temp_chromosomes_seen;
		}
	} else {
		die "No ESPs could be loaded -> check input file format!\n";
	}
	
}

sub overlapRepeats {

	my($portion) = @_;

	my $temp_countDiscardRepeats = 0; my $temp_countTagRepeats = 0; my $discard = 'no'; my $tag = 'no';

	# If we will discard mappings in repeats...
	if ($DISCARD_MAPPINGS_IN_REPEATS ne 'no') {
	
		while ($portion =~ /[$DISCARD_REPEATS_LIST]/g) {
			$temp_countDiscardRepeats++;
		}
		
		if (($temp_countDiscardRepeats*100/length($portion)) > $DISCARD_MAPPINGS_IN_REPEATS) {
			$discard = 'yes';
		}
		
	}

	# If we will tag mappings in repeats...	
	if ($TAG_MAPPINGS_IN_REPEATS ne 'no') {
	
		if (($DISCARD_MAPPINGS_IN_REPEATS ne 'no') and ($DISCARD_REPEATS_LIST eq $TAG_REPEATS_LIST)) {
			$temp_countTagRepeats = $temp_countDiscardRepeats;
		} else {

			while ($portion =~ /[$TAG_REPEATS_LIST]/g) {
				$temp_countTagRepeats++;
			}
			
		}
		
		if (($temp_countTagRepeats*100/length($portion)) > $TAG_MAPPINGS_IN_REPEATS) {
			$tag = 'yes';
		}
		
	}	

	return($discard, $tag);

}

sub overlapSegdups {

	my($portion) = @_;

	my $temp_countSegDups = 0; my $discard = 'no'; my $tag = 'no';

	# If we will discard mappings in segdups...
	if ($DISCARD_MAPPINGS_IN_SEGDUPS ne 'no') {

		while ($portion =~ /G/g) {
			$temp_countSegDups++;
		}
		
		if (($temp_countSegDups*100/length($portion)) > $DISCARD_MAPPINGS_IN_SEGDUPS) {
			$discard = 'yes';
		}
		
	}
		
	# If we will tag mappings in segdups...
	if ($TAG_MAPPINGS_IN_SEGDUPS ne 'no') {
	
		unless ($DISCARD_MAPPINGS_IN_SEGDUPS ne 'no') {

			while ($portion =~ /G/g) {
				$temp_countSegDups++;
			}
			
		}
		
		if (($temp_countSegDups*100/length($portion)) > $TAG_MAPPINGS_IN_SEGDUPS) {
			$tag = 'yes';
		}
		
	}

	return($discard, $tag);

}

sub set_overlap {

	my($StartLeft, $EndLeft, $StartRight, $EndRight) = @_;

	if ( ($EndLeft > $StartRight) && ( (($EndLeft-$StartRight)*100/($EndLeft-$StartLeft) >= $OVERLAPPINGENDS_PERC) 
		|| (($EndLeft-$StartRight)*100/($EndRight-$StartRight) >= $OVERLAPPINGENDS_PERC)) ) {

		return(1);
		
	} else {
	
		return(0);

	}
	
}

sub set_sumval {

	my($StartLeft, $EndLeft, $StartRight, $EndRight, $strand) = @_;
	
	if ($strand eq '+') {
	
		return($StartLeft+$StartRight);
	
	} else {
	
		return($EndLeft+$EndRight);
	
	}

}

sub clustering { 

	my($by_chr_and_strand) = @_;

	my $numESPs = scalar(@{$ESP_INFO{$by_chr_and_strand}});
	
	# CANDIDATE CLUSTERS -------
	
#@#	print localtime( ) . " ... finished sorting the data\n";

	# Generate candidate clusters
	@CLUSTERS_CANDIDATES = ();
	PEMS: for (my $i=0; $i<$numESPs; $i++) {	# loop through PEMs, smaller to larger coords for ++, larger to smaller for --

		my @temp_candidates_withthisPEM = ();
		my %temp_compatiblePEMS = ();
		
		CLUSTERS: for (my $j=$#CLUSTERS_CANDIDATES; $j>=0; $j--) {	# loop through candidate clusters, larger to smaller coords
										# for ++, smaller to larger for --
									
			my @temp_compatiblePEMS_inthiscluster = (); 	# store only indexes here
			my @temp_sumvalues = ();			# store sum values here
			my $temp_support = 0;				# keep adding (current after finishing loop)
			my $temp_supportClean = 0;			# keep adding (current after finishing loop)
			my $temp_weighted = 0;				# keep adding (current after finishing loop)
			my $temp_lastpairvalidation = 1;		# if eventually turns 0 --> stop comparing to available clusters
			my $temp_numindexes_inthiscluster = scalar(@{$CLUSTERS_CANDIDATES[$j]->[0]});
			my $temp_extremecoords = {	
				leftStart => undef, leftStart1 => undef, leftStart2 => undef, 
				leftEnd => undef, leftEnd1 => undef, leftEnd2 => undef, 
				rightStart => undef, rightStart1 => undef, rightStart2 => undef, 
				rightEnd => undef, rightEnd1 => undef, rightEnd2 => undef, 
			};
			my $temp_singlemappings = 0;
			my $temp_tags = '';

			# Calculate min/max values only from $i
			($temp_extremecoords) = calculate_minmaxcoords($temp_extremecoords, $ESP_INFO{$by_chr_and_strand}->[$i]);


			INDEXES: for (my $k=$temp_numindexes_inthiscluster-1; $k>=0; $k--) {	# loop through PEM indexes, larger to smaller
												# for ++, smaller to larger for --
				# Check compatibility of PEM $i to each of PEMs $k in $j
				my $temp_pairvalidation = pair_validation($ESP_INFO{$by_chr_and_strand}->[$i], $ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]);
				if ($temp_pairvalidation eq 'last') {

					if ($k == ($temp_numindexes_inthiscluster-1)) {
						$temp_lastpairvalidation = 0;
					}
					
					last INDEXES; # no compatible by distance any more!

				} elsif ($temp_pairvalidation eq 'next') {

					next INDEXES; # no compatible by rules with this PEM, but yes by distance; check another PEM

				} else {

					# compatible! add to the BEGINNING of the list
					unshift(@temp_compatiblePEMS_inthiscluster, $CLUSTERS_CANDIDATES[$j]->[0]->[$k]);
					unshift(@temp_sumvalues, $ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]->[9]);
					
					# check and register copies in $ESP_INFO and calculate weighted
					if (duplicate_pair($ESP_INFO{$by_chr_and_strand}->[$i], $ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]) == 1) {
						my %temp_icopies = split(/\./, $ESP_INFO{$by_chr_and_strand}->[$i]->[10]);
						my %temp_kcopies = split(/\./, $ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]->[10]);
						unless (exists $temp_icopies{$CLUSTERS_CANDIDATES[$j]->[0]->[$k]}) {
							$ESP_INFO{$by_chr_and_strand}->[$i]->[10] .= $CLUSTERS_CANDIDATES[$j]->[0]->[$k].'..';
						}
						unless (exists $temp_kcopies{$i}) {
							$ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]->[10] .= $i.'..';
						}
					
					
					} 
					
					# Calculate support and weighted
					$temp_support++;
					$temp_supportClean += 1/(scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]->[10]))+1);
					if ($WEIGHT_PEM_SUPPORT eq 'yes') {
					$temp_weighted += 1/((scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]->[10]))+1) * $ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]->[12]);
					} else {
					$temp_weighted += 1/(scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]->[10]))+1);
					}
					
					# Calculate min/max values
					($temp_extremecoords) = calculate_minmaxcoords($temp_extremecoords, $ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]);
					
					# Regarding the # of mappings of this PEM
					if ($ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]->[12] == 1) {
						$temp_singlemappings++;
					}
					
					# Add tags of this PEM
					$temp_tags .= $ESP_INFO{$by_chr_and_strand}->[ $CLUSTERS_CANDIDATES[$j]->[0]->[$k] ]->[14];

				}					
			
			}
			
			# Are there compatibles in this cluster? All of PEMs or just a subset?
			if ($temp_support == 0) {

				# no compatibles in this cluster; maybe try next (if still compatible by distance)				

			} elsif ($temp_support == $temp_numindexes_inthiscluster) {

				# current PEM is compatible to all PEMs in the cluster --> add current PEM to the cluster
				push(@{$CLUSTERS_CANDIDATES[$j]->[0]}, $i); # $i is added at the end!
				$temp_support++; # for $i
				$temp_supportClean += 1/(scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[$i]->[10]))+1);# for $i
				if ($WEIGHT_PEM_SUPPORT eq 'yes') {
				$temp_weighted += 1/((scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[$i]->[10]))+1) * $ESP_INFO{$by_chr_and_strand}->[$i]->[12]);# for $i
				} else {
				$temp_weighted += 1/(scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[$i]->[10]))+1);# for $i
				}
				
				$CLUSTERS_CANDIDATES[$j]->[1] = $temp_supportClean;
				$CLUSTERS_CANDIDATES[$j]->[2] = $temp_weighted;
				
				# Store extreme coords
				$CLUSTERS_CANDIDATES[$j]->[5] = $temp_extremecoords->{leftStart};
				$CLUSTERS_CANDIDATES[$j]->[6] = $temp_extremecoords->{leftEnd};
				$CLUSTERS_CANDIDATES[$j]->[7] = $temp_extremecoords->{rightStart};
				$CLUSTERS_CANDIDATES[$j]->[8] = $temp_extremecoords->{rightEnd};
				$CLUSTERS_CANDIDATES[$j]->[13] = [$temp_extremecoords->{leftStart1}, $temp_extremecoords->{leftStart2}, $temp_extremecoords->{leftEnd1}, $temp_extremecoords->{leftEnd2}, $temp_extremecoords->{rightStart1}, $temp_extremecoords->{rightStart2}, $temp_extremecoords->{rightEnd1}, $temp_extremecoords->{rightEnd2}];
																			
				# Calculate the variance of sum values
				unshift(@temp_sumvalues, $ESP_INFO{$by_chr_and_strand}->[$i]->[9]);
				($CLUSTERS_CANDIDATES[$j]->[3], $CLUSTERS_CANDIDATES[$j]->[4]) = calculate_sumvariance(@temp_sumvalues);
				
				# Regarding the # of mappings of this PEM
				if ($ESP_INFO{$by_chr_and_strand}->[$i]->[12] == 1) {
					$temp_singlemappings++;
				}
				$CLUSTERS_CANDIDATES[$j]->[11] = $temp_singlemappings;
				
				# Add tags of this PEM
				$temp_tags .= $ESP_INFO{$by_chr_and_strand}->[$i]->[14];
#v15#				$temp_tags =~ s/(.)(?=.*?\1)//g;
#v15#				$CLUSTERS_CANDIDATES[$j]->[12] = $temp_tags;

				$temp_tags_store='';
				if (($TAG_MAPPINGS_IN_REPEATS_ALL eq 'no') and ($temp_tags =~ /\^/)) {
					$temp_tags_store .= '^';
				} elsif ($TAG_MAPPINGS_IN_REPEATS_ALL eq 'yes') {
					$nimim=0;
					while ($temp_tags =~ /\^/g) {
						$nimim++;
					}
					if ($nimim == $temp_support) {
					$temp_tags_store .= '^';
					}
				} else {
				}
				
				if (($TAG_MAPPINGS_IN_SEGDUPS_ALL eq 'no') and ($temp_tags =~ /:/)) {
					$temp_tags_store .= ':';
				} elsif ($TAG_MAPPINGS_IN_SEGDUPS_ALL eq 'yes') {
					$nimim=0;
					while ($temp_tags =~ /:/g) {
						$nimim++;
					}
					if ($nimim == $temp_support) {
					$temp_tags_store .= ':';
					}
				} else {
				}
				$CLUSTERS_CANDIDATES[$j]->[12] = $temp_tags_store;
				
				# Add PEMs of this cluster to compatible PEMs for $i and add 1 to mergedTimes
				@temp_compatiblePEMS{@temp_compatiblePEMS_inthiscluster} = undef;
				$temp_compatiblePEMS{$i} = undef;
				$ESP_INFO{$by_chr_and_strand}->[$i]->[11]++;
			
			} else {
				# current PEM is compatible to a subset of PEMs in the cluster --> create new cluster with them
				# ONLY IF IT IS NOT A PARTIAL/COMPLETE REDUNDANT CLUSTER
				if (redundant_cluster(\@temp_compatiblePEMS_inthiscluster, \%temp_compatiblePEMS) == 0) {
				
					push(@temp_compatiblePEMS_inthiscluster, $i); # $i is added at the end!
					$temp_support++;
					$temp_supportClean += 1/(scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[$i]->[10]))+1);
					if ($WEIGHT_PEM_SUPPORT eq 'yes') {
					$temp_weighted += 1/((scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[$i]->[10]))+1) * $ESP_INFO{$by_chr_and_strand}->[$i]->[12]);
					} else {
					$temp_weighted += 1/(scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[$i]->[10]))+1);
					}
					
					# Calculate the variance of sum values
					unshift(@temp_sumvalues, $ESP_INFO{$by_chr_and_strand}->[$i]->[9]);
					my($temp_summean, $temp_sumvar) = calculate_sumvariance(@temp_sumvalues);
						
					# Regarding the # of mappings of this PEM
					if ($ESP_INFO{$by_chr_and_strand}->[$i]->[12] == 1) {
						$temp_singlemappings++;
					}

					# Add tags of this PEM
					$temp_tags .= $ESP_INFO{$by_chr_and_strand}->[$i]->[14];
#v15#					$temp_tags =~ s/(.)(?=.*?\1)//g;

					$temp_tags_store = '';
					if (($TAG_MAPPINGS_IN_REPEATS_ALL eq 'no') and ($temp_tags =~ /\^/)) {
						$temp_tags_store .= '^';
					} elsif ($TAG_MAPPINGS_IN_REPEATS_ALL eq 'yes') {
						$nimim=0;
						while ($temp_tags =~ /\^/g) {
							$nimim++;
						}
						if ($nimim == $temp_support) {
						$temp_tags_store .= '^';
						}
					} else {
					}
				
					if (($TAG_MAPPINGS_IN_SEGDUPS_ALL eq 'no') and ($temp_tags =~ /:/)) {
						$temp_tags_store .= ':';
					} elsif ($TAG_MAPPINGS_IN_SEGDUPS_ALL eq 'yes') {
						$nimim=0;
						while ($temp_tags =~ /:/g) {
							$nimim++;
						}
						if ($nimim == $temp_support) {
						$temp_tags_store .= ':';
						}
					} else {
					}

					# Add new cluster and add PEMs of this cluster to compatible PEMs for $i
					push(@CLUSTERS_CANDIDATES, [ \@temp_compatiblePEMS_inthiscluster, $temp_supportClean, $temp_weighted, $temp_summean, $temp_sumvar, $temp_extremecoords->{leftStart}, $temp_extremecoords->{leftEnd}, $temp_extremecoords->{rightStart}, $temp_extremecoords->{rightEnd}, undef, undef, $temp_singlemappings, $temp_tags_store, [ $temp_extremecoords->{leftStart1}, $temp_extremecoords->{leftStart2}, $temp_extremecoords->{leftEnd1}, $temp_extremecoords->{leftEnd2}, $temp_extremecoords->{rightStart1}, $temp_extremecoords->{rightStart2}, $temp_extremecoords->{rightEnd1}, $temp_extremecoords->{rightEnd2} ] ]);
					@temp_compatiblePEMS{@temp_compatiblePEMS_inthiscluster} = undef;
					
					# Add 1 to mergedTimes for each PEM in @temp_compatiblePEMS_inthiscluster
					foreach my $l (@temp_compatiblePEMS_inthiscluster) {
						$ESP_INFO{$by_chr_and_strand}->[$l]->[11]++;
					}
				}
				
					
			}
					
			# If last PEM was not compatible by distance, finish this $i PEM and go to next
			last CLUSTERS if ($temp_lastpairvalidation == 0);
		
		}
		
		# Was PEM $i used somewhere?
		if ($ESP_INFO{$by_chr_and_strand}->[$i]->[11] == 0) {

			# never merged --> create new cluster with PEM $i
			
			# Calculate min/max values only from $i
			my $temp_extremecoords = {	
				leftStart => undef, leftStart1 => undef, leftStart2 => undef, 
				leftEnd => undef, leftEnd1 => undef, leftEnd2 => undef, 
				rightStart => undef, rightStart1 => undef, rightStart2 => undef, 
				rightEnd => undef, rightEnd1 => undef, rightEnd2 => undef, 
			};

			# Regarding the # of mappings of this PEM
			if ($ESP_INFO{$by_chr_and_strand}->[$i]->[12] == 1) {
				$temp_singlemappings = 1;
			} else {
				$temp_singlemappings = 0;
			}

			# Add tags of this PEM
			$temp_tags = $ESP_INFO{$by_chr_and_strand}->[$i]->[14];
#v15#			$temp_tags =~ s/(.)(?=.*?\1)//g;

			# Calculate min/max values only from $i
			($temp_extremecoords) = calculate_minmaxcoords($temp_extremecoords, $ESP_INFO{$by_chr_and_strand}->[$i]);
			
			# Calculate weighted support
			if ($WEIGHT_PEM_SUPPORT eq 'yes') {
			$temp_weighted = 1/$ESP_INFO{$by_chr_and_strand}->[$i]->[12];
			} else {
			$temp_weighted = 1;
			}

			push(@CLUSTERS_CANDIDATES, [ [ $i ], 1, $temp_weighted, $ESP_INFO{$by_chr_and_strand}->[$i]->[9], 0, $temp_extremecoords->{leftStart}, $temp_extremecoords->{leftEnd}, $temp_extremecoords->{rightStart}, $temp_extremecoords->{rightEnd}, undef, undef, $temp_singlemappings, $temp_tags, [ $temp_extremecoords->{leftStart1}, $temp_extremecoords->{leftStart2}, $temp_extremecoords->{leftEnd1}, $temp_extremecoords->{leftEnd2}, $temp_extremecoords->{rightStart1}, $temp_extremecoords->{rightStart2}, $temp_extremecoords->{rightEnd1}, $temp_extremecoords->{rightEnd2} ] ]);
			$ESP_INFO{$by_chr_and_strand}->[$i]->[11]++;
		
		} 
		
	}
	

	# FINAL CLUSTERS -------
	
	# Sort the candidate clusters by PEM weighted support (desc) and by variance of sum coefficient (asc)
	@CLUSTERS_CANDIDATES = sort { $b->[2] <=> $a->[2] || $a->[4] <=> $b->[4] } @CLUSTERS_CANDIDATES;

	# Generate final clusters
	my $temp_clusters_final = 0;
	
	do {
	
		# Check indexes in the first candidate --> do they need to be removed from other candidate clusters?
		my %temp_delindexes = ();
		foreach my $index (@{ $CLUSTERS_CANDIDATES[0]->[0] }) { 
			if ($ESP_INFO{$by_chr_and_strand}->[$index]->[11] > 1) {
				$temp_delindexes{$index} = $ESP_INFO{$by_chr_and_strand}->[$index]->[11];
			}
	
		}
		
		# Print cluster into output
		if ($CLUSTERS_CANDIDATES[0]->[2] >= $MIN_SUPPORT_CLUSTERS) {
		
			my $temp_alsoprint = '';
			
			unless ($CLUSTERS_CANDIDATES[0]->[6] < $CLUSTERS_CANDIDATES[0]->[7]) {   # Never should happen
				
				# We tag with ! clusters that needed correction of coordinate leftEnd or rightStart, but then they will be used
				# as other clusters
				$temp_alsoprint = '!';  
											
				# Correct coordinates
				if ($by_chr_and_strand =~ /\+$/) {
				
					$CLUSTERS_CANDIDATES[0]->[6] = $CLUSTERS_CANDIDATES[0]->[7] - 1;
				
				} else {
				
					$CLUSTERS_CANDIDATES[0]->[7] = $CLUSTERS_CANDIDATES[0]->[6] + 1;
				
				}
				
			} 
		
			$temp_clusters_final++;
		
			print {filehandles('CLUST', $by_chr_and_strand)} $by_chr_and_strand . "\t" . ($temp_clusters_final-1) . "\t" . 
			(join(',', @{ $CLUSTERS_CANDIDATES[0]->[0] })) . "\t" .
			$CLUSTERS_CANDIDATES[0]->[1] . "\t" . $CLUSTERS_CANDIDATES[0]->[2] . "\t" .  $CLUSTERS_CANDIDATES[0]->[3] . "\t" .  
			$CLUSTERS_CANDIDATES[0]->[4] . "\t" .  $CLUSTERS_CANDIDATES[0]->[5] . "\t" .  $CLUSTERS_CANDIDATES[0]->[6] . "\t" .  
			$CLUSTERS_CANDIDATES[0]->[7] . "\t" .  $CLUSTERS_CANDIDATES[0]->[8] . "\t" .  $CLUSTERS_CANDIDATES[0]->[11] . "\t" .  
			$CLUSTERS_CANDIDATES[0]->[12].$temp_alsoprint . "\t" . (join(',', @{ $CLUSTERS_CANDIDATES[0]->[13] })) . "\n";
			
			# Write all PEMs in files (name with assigned id & id of assigned cluster)
			if ($OUTPUT_PEM_INDEXES eq 'yes') {
				foreach my $pem (@{ $CLUSTERS_CANDIDATES[0]->[0] }) {
				print {filehandles('PEMS', $by_chr_and_strand)} "$by_chr_and_strand\t$pem\t".
				$ESP_INFO{$by_chr_and_strand}->[$pem]->[0]."\t".$ESP_INFO{$by_chr_and_strand}->[$pem]->[1]."\t".
				$ESP_INFO{$by_chr_and_strand}->[$pem]->[12]."\t".($temp_clusters_final-1)."\t".$ESP_INFO{$by_chr_and_strand}->[$pem]->[10]."\t".$ESP_INFO{$by_chr_and_strand}->[$pem]->[14]."\n";
				}
			}			
		
		}

		# Push first candidate to final clusters
		shift @CLUSTERS_CANDIDATES;
		
		# Recalculate other candidates
		if (scalar(keys %temp_delindexes) > 0) {
		
			# Remove indexes from other candidates
			my $g = 0;
			while ($g <= $#CLUSTERS_CANDIDATES) {
			
				my @temp_indexesincandidate = ();
				my @temp_sumvalues = ();
				my $temp_support = 0;
				my $temp_supportClean = 0;
				my $temp_weighted = 0;
				my $temp_extremecoords = {	
					leftStart => undef, leftStart1 => undef, leftStart2 => undef, 
					leftEnd => undef, leftEnd1 => undef, leftEnd2 => undef, 
					rightStart => undef, rightStart1 => undef, rightStart2 => undef, 
					rightEnd => undef, rightEnd1 => undef, rightEnd2 => undef, 
				};
				my $temp_hadtodelete = 0;
				my $temp_singlemappings = 0;
				my $temp_tags = '';
							
				foreach my $index (@{ $CLUSTERS_CANDIDATES[$g]->[0] }) {
				
					if ($temp_delindexes{$index} > 1) {
					
						$temp_hadtodelete = 1;
						$temp_delindexes{$index}--;
						if ($temp_delindexes{$index} == 1) {
							delete $temp_delindexes{$index};
						}
					
					} else {
					
						push(@temp_indexesincandidate, $index);
						push(@temp_sumvalues, $ESP_INFO{$by_chr_and_strand}->[$index]->[9]);
						$temp_support++;
						$temp_supportClean += 1/(scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[$index]->[10]))+1);
						if ($WEIGHT_PEM_SUPPORT eq 'yes') {
						$temp_weighted += 1/((scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[$index]->[10]))+1) * $ESP_INFO{$by_chr_and_strand}->[$index]->[12]);
						} else {
						$temp_weighted += 1/(scalar(split(/\.\./, $ESP_INFO{$by_chr_and_strand}->[$index]->[10]))+1);
						}
						
						($temp_extremecoords) = calculate_minmaxcoords($temp_extremecoords, $ESP_INFO{$by_chr_and_strand}->[$index]);
						# Regarding the # of mappings of this PEM
						if ($ESP_INFO{$by_chr_and_strand}->[$index]->[12] == 1) {
							$temp_singlemappings++;
						}
						
						# Add tags of this PEM
						$temp_tags .= $ESP_INFO{$by_chr_and_strand}->[$index]->[14];

					}
			
				}
				
				# rewrite info for this candidate
				if (scalar(@temp_indexesincandidate) == 0) {   # I have to delete this candidate

					splice(@CLUSTERS_CANDIDATES, $g, 1);
				
				} else {
				
					if ($temp_hadtodelete == 1) {

					my($temp_summean, $temp_sumvar) = calculate_sumvariance(@temp_sumvalues);
					
					# Manage tags
#v15#					$temp_tags =~ s/(.)(?=.*?\1)//g;

					$temp_tags_store = '';
					if (($TAG_MAPPINGS_IN_REPEATS_ALL eq 'no') and ($temp_tags =~ /\^/)) {
						$temp_tags_store .= '^';
					} elsif ($TAG_MAPPINGS_IN_REPEATS_ALL eq 'yes') {
						$nimim=0;
						while ($temp_tags =~ /\^/g) {
							$nimim++;
						}
						if ($nimim == $temp_support) {
						$temp_tags_store .= '^';
						}
					} else {
					}
				
					if (($TAG_MAPPINGS_IN_SEGDUPS_ALL eq 'no') and ($temp_tags =~ /:/)) {
						$temp_tags_store .= ':';
					} elsif ($TAG_MAPPINGS_IN_SEGDUPS_ALL eq 'yes') {
						$nimim=0;
						while ($temp_tags =~ /:/g) {
							$nimim++;
						}
						if ($nimim == $temp_support) {
						$temp_tags_store .= ':';
						}
					} else {
					}
					
					$CLUSTERS_CANDIDATES[$g] = [ \@temp_indexesincandidate, $temp_supportClean, $temp_weighted, $temp_summean, $temp_sumvar, $temp_extremecoords->{leftStart}, $temp_extremecoords->{leftEnd}, $temp_extremecoords->{rightStart}, $temp_extremecoords->{rightEnd}, undef, undef, $temp_singlemappings, $temp_tags_store, [ $temp_extremecoords->{leftStart1}, $temp_extremecoords->{leftStart2}, $temp_extremecoords->{leftEnd1}, $temp_extremecoords->{leftEnd2}, $temp_extremecoords->{rightStart1}, $temp_extremecoords->{rightStart2}, $temp_extremecoords->{rightEnd1}, $temp_extremecoords->{rightEnd2} ] ];
					
					}
					
					$g++;
				
				}
								
				# Can we stop here??
				last if (scalar(keys %temp_delindexes) == 0);
				
			}
			
			# Sort again candidates
			if (scalar(@CLUSTERS_CANDIDATES) > 1) {
				@CLUSTERS_CANDIDATES = sort { $b->[2] <=> $a->[2] || $a->[4] <=> $b->[4] } @CLUSTERS_CANDIDATES;
			}
		
		}
	
	} until (scalar(@CLUSTERS_CANDIDATES) == 0);
	
	undef @CLUSTERS_CANDIDATES;
	
#@#	print localtime( ) . " ... finished generating final clusters\n";

	print $by_chr_and_strand."\t".$numESPs." PEMs, ".$temp_clusters_final." clusters\n"; 

}

sub get_extreme {

	my($esp) = @_;
	
	if ($esp->[7] eq "+") {
		return($esp->[3]);
	}
	else {
		return($esp->[6]);
	}
	
}

sub pair_validation {

	my($ESP_i, $ESP_j) = @_;

	if ($ESP_i->[7] eq '+') {   			
					
		# Stop if max value exceeded! -> FIRST RULE & USEFUL TO STOP THE LOOP WHEN MAX VALUE EXCEEDED
		return('last') if ( abs($ESP_j->[3]-$ESP_i->[3]) > $DATA_STAT{max} );
			
		# Check OTHER 4 CLUSTERING RULES (INCLUDING SUM RULE)
		if ( ($ESP_j->[3] < $ESP_i->[5])
			&& ($ESP_j->[5] > $ESP_i->[3])
			&& (abs($ESP_i->[5]-$ESP_j->[5]) < $DATA_STAT{max} )
			&& (abs($ESP_j->[9]-$ESP_i->[9]) < $DATA_STAT{range} )) {
			return('ok');
		} else {
			return('next');
		}	
						
	} else {
	
		# Stop if max value exceeded! -> FIRST RULE & USEFUL TO STOP THE LOOP WHEN MAX VALUE EXCEEDED
		return('last') if ( abs($ESP_i->[6]-$ESP_j->[6]) > $DATA_STAT{max} );
			
		# Check OTHER 4 CLUSTERING RULES (INCLUDING SUM RULE)
		if ( ($ESP_j->[6] > $ESP_i->[4])
			&& ($ESP_j->[4] < $ESP_i->[6])
			&& (abs($ESP_j->[4]-$ESP_i->[4]) < $DATA_STAT{max} )
			&& (abs($ESP_j->[9]-$ESP_i->[9]) < $DATA_STAT{range} )){
			return('ok');
		} else {
			return('next');
		}
			
	}

}

sub duplicate_pair {

	my($ESP_i, $ESP_j) = @_;
	
	return(0) if (($ESP_i->[0] ne $ESP_j->[0]) or ($ESP_i->[7] ne $ESP_j->[7]) or ($ESP_i->[8] ne $ESP_j->[8]));
	
	my $set_duplicate_bp;
	
	if ($DUPLICATEPEMS_BP_LIBRARY{$ESP_i->[0]} =~ /^\d+$/) {

		$set_duplicate_bp = $DUPLICATEPEMS_BP_LIBRARY{$ESP_i->[0]};

	} else {

		$set_duplicate_bp = $DUPLICATEPEMS_BP;

	}

		
	if ($ESP_i->[7] eq '+') {
	
		if (	(abs($ESP_i->[3] - $ESP_j->[3]) <= $set_duplicate_bp)
			&& (abs($ESP_i->[5] - $ESP_j->[5]) <= $set_duplicate_bp) 
			&& ($ESP_i->[0] eq $ESP_j->[0]) 
			&& ($ESP_i->[7] eq $ESP_j->[7]) 
			&& ($ESP_i->[8] eq $ESP_j->[8])	) {
			
			return(1);  # they are duplicates!
		
		} else {
		
			return(0);  # they are notduplicates!
		
		}
		
	} else {

		if (	(abs($ESP_i->[6] - $ESP_j->[6]) <= $set_duplicate_bp)
			&& (abs($ESP_i->[4] - $ESP_j->[4]) <= $set_duplicate_bp) 
			&& ($ESP_i->[0] eq $ESP_j->[0]) 
			&& ($ESP_i->[7] eq $ESP_j->[7]) 
			&& ($ESP_i->[8] eq $ESP_j->[8])	) {
			
			return(1);  # they are duplicates!
		
		} else {
		
			return(0);  # they are notduplicates!
		
		}
		
	} 

}

sub calculate_sumvariance {

	my(@sums) = @_;
	
	if ((scalar(@sums)) == 1) {
	
		return($sums[0], 0);
	
	} else {
	
		my $stat = Statistics::Descriptive::Full->new();
		$stat->add_data(@sums);
		return($stat->mean(), $stat->variance());	
	
	}

}

sub redundant_cluster {

	my($temp_compatiblePEMS_inthiscluster, $temp_compatiblePEMS) = @_;
	
	foreach my $PEM (@$temp_compatiblePEMS_inthiscluster) {
	
		if (not exists $temp_compatiblePEMS->{$PEM}) {
		
			return(0);
		
		}
	
	}
	
	return(1);

}

sub calculate_minmaxcoords {

	my($extremes, $ESP_i) = @_;
	
	if ($ESP_i->[7] eq '+') {   # només compten $ESP_i->[3] i $ESP_i->[5]
	
		if (($ESP_i->[3] < $extremes->{leftStart}) or ($extremes->{leftStart} eq undef)) {
			$extremes->{leftStart} = $ESP_i->[3];
			$extremes->{leftStart1} = $ESP_i->[3];
			$extremes->{leftStart2} = $ESP_i->[4];
		}
	
		if (($ESP_i->[3] > $extremes->{leftEnd}) or ($extremes->{leftEnd} eq undef)) {
			$extremes->{leftEnd} = $ESP_i->[3];
			$extremes->{leftEnd1} = $ESP_i->[3];
			$extremes->{leftEnd2} = $ESP_i->[4];
		}

		if (($ESP_i->[5] < $extremes->{rightStart}) or ($extremes->{rightStart} eq undef)) {
			$extremes->{rightStart} = $ESP_i->[5];
			$extremes->{rightStart1} = $ESP_i->[5];
			$extremes->{rightStart2} = $ESP_i->[6];
		}

		if (($ESP_i->[5] > $extremes->{rightEnd}) or ($extremes->{rightEnd} eq undef)) {
			$extremes->{rightEnd} = $ESP_i->[5];
			$extremes->{rightEnd1} = $ESP_i->[5];
			$extremes->{rightEnd2} = $ESP_i->[6];
		}
	
	} else {   # només compten $ESP_i->[4] i $ESP_i->[6]
	
		if (($ESP_i->[4] < $extremes->{leftStart}) or ($extremes->{leftStart} eq undef)) {
			$extremes->{leftStart} = $ESP_i->[4];
			$extremes->{leftStart1} = $ESP_i->[3];
			$extremes->{leftStart2} = $ESP_i->[4];
		}
	
		if (($ESP_i->[4] > $extremes->{leftEnd}) or ($extremes->{leftEnd} eq undef)) {
			$extremes->{leftEnd} = $ESP_i->[4];
			$extremes->{leftEnd1} = $ESP_i->[3];
			$extremes->{leftEnd2} = $ESP_i->[4];
		}

		if (($ESP_i->[6] < $extremes->{rightStart}) or ($extremes->{rightStart} eq undef)) {
			$extremes->{rightStart} = $ESP_i->[6];
			$extremes->{rightStart1} = $ESP_i->[5];
			$extremes->{rightStart2} = $ESP_i->[6];
		}

		if (($ESP_i->[6] > $extremes->{rightEnd}) or ($extremes->{rightEnd} eq undef)) {
			$extremes->{rightEnd} = $ESP_i->[6];
			$extremes->{rightEnd1} = $ESP_i->[5];
			$extremes->{rightEnd2} = $ESP_i->[6];
		}	
	
	}
				
	return($extremes);

}

sub filehandles {

	my($data, $chrstrand_id, $definition) = @_;
	$chrstrand_id =~ s/\+$/POS/;
	$chrstrand_id =~ s/-$/NEG/;
	$chrstrand_id =~ s/\w+//;
	return('*FH'.uc($data).uc($chrstrand_id).uc($definition));

}

sub openfiles_clustering {

	my($by_chr_and_strand) = @_;
	
	open(filehandles('CLUST', $by_chr_and_strand), '>'.$OUTPUT_PATH.$OUTPUT_PREFIX.$by_chr_and_strand.'.clusters');
	
	if ($OUTPUT_PEM_INDEXES eq 'yes') {
		open(filehandles('PEMS', $by_chr_and_strand), '>'.$OUTPUT_PATH.$OUTPUT_PREFIX.$by_chr_and_strand.'.PEMids');
	}

}

sub closefiles_clustering {

	my($by_chr_and_strand) = @_;
	
	close filehandles('CLUST', $by_chr_and_strand);
	
	if ($OUTPUT_PEM_INDEXES eq 'yes') {
		close filehandles('PEMS', $by_chr_and_strand);
	}

}

sub openfiles_inversions {

	my($chromosome) = @_;			
	
	if (($BP_DEFINITION eq 'wide') or ($BP_DEFINITION eq 'all')) {
		open(filehandles('INV', $chromosome, 'BP2'), '>'.$OUTPUT_PATH.$OUTPUT_PREFIX.$chromosome.'.inversions.wide');
	}
	if (($BP_DEFINITION eq 'narrow') or ($BP_DEFINITION eq 'all')) {
		open(filehandles('INV', $chromosome, 'BP3'), '>'.$OUTPUT_PATH.$OUTPUT_PREFIX.$chromosome.'.inversions.narrow');
	}

}

sub closefiles_inversions {

	my($chromosome) = @_;
	
	if (($BP_DEFINITION eq 'wide') or ($BP_DEFINITION eq 'all')) {
		close filehandles('INV', $chromosome, 'BP2');
	}
	if (($BP_DEFINITION eq 'narrow') or ($BP_DEFINITION eq 'all')) {
		close filehandles('INV', $chromosome, 'BP3');
	}	

}

sub load_clusters {

	my($chromosome) = @_;
	
	$SUMMARY_RESULTS->{clusters}->{'+'} = 0;
	$SUMMARY_RESULTS->{clusters_neededcorrection}->{'+'} = 0;
	$SUMMARY_RESULTS->{clusters}->{'-'} = 0;
	$SUMMARY_RESULTS->{clusters_neededcorrection}->{'-'} = 0;
	
	open(LOADPOS, $OUTPUT_PATH.$OUTPUT_PREFIX.$chromosome.'+'.'.clusters');
	@CLUSTERS_POS = ();
	
	while (<LOADPOS>) {
		
		next if ($_ !~ /\S+\t\d+\t[\d,]+\t[\d\.]+\t[\d\.]+\t[\d\.]+\t[\d\.]+\t\d+\t\d+\t\d+\t\d+\t\d+\t\S*\t[\d,]+/);

		if ($_ =~ /^!/) {  # These clusters needed correction to leftEnd coordinate, but they are used as others!
			$SUMMARY_RESULTS->{clusters_neededcorrection}->{'+'}++;
		}
		
		$SUMMARY_RESULTS->{clusters}->{'+'}++;
		push(@CLUSTERS_POS, load_clusters_readline($_));
	
	}
	
	close LOADPOS;
	
	open(LOADNEG, $OUTPUT_PATH.$OUTPUT_PREFIX.$chromosome.'-'.'.clusters');
	@CLUSTERS_NEG = ();
	
	while (<LOADNEG>) {
	
		next if ($_ !~ /\S+\t\d+\t[\d,]+\t[\d\.]+\t[\d\.]+\t[\d\.]+\t[\d\.]+\t\d+\t\d+\t\d+\t\d+\t\d+\t\S*\t[\d,]+/);

		if ($_ =~ /^!/) {  # These clusters needed correction to leftEnd coordinate, but they are used as others!
			$SUMMARY_RESULTS->{clusters_neededcorrection}->{'-'}++;
		}
		
		$SUMMARY_RESULTS->{clusters}->{'-'}++;
		push(@CLUSTERS_NEG, load_clusters_readline($_));
	
	}
	
	close LOADNEG;
		
}

sub load_clusters_readline {

	my($line) = @_;
	
	chomp $line;
	my @temp_fields = split /\t/, $line;
	my @temp_ids = split /,/, $temp_fields[2];
	my @temp_coords = split /,/, $temp_fields[13];	
	
	return([ \@temp_ids, $temp_fields[3], $temp_fields[4], $temp_fields[5], $temp_fields[6], $temp_fields[7], $temp_fields[8], $temp_fields[9], $temp_fields[10], 0, $temp_fields[1], $temp_fields[11], $temp_fields[12], \@temp_coords ]);

}

sub predict_inversions {

	my($chromosome) = @_;
	
	$SUMMARY_RESULTS->{inversions}->{both} = 0;
	$SUMMARY_RESULTS->{inversions}->{'+'} = 0;
	$SUMMARY_RESULTS->{inversions}->{'-'} = 0;
	$SUMMARY_RESULTS->{discard_A}->{1} = 0;
	$SUMMARY_RESULTS->{discard_A}->{2} = 0;
	$SUMMARY_RESULTS->{discard_A}->{3} = 0;
	$SUMMARY_RESULTS->{discard_B}->{1} = 0;
	$SUMMARY_RESULTS->{discard_B}->{2} = 0;
	$SUMMARY_RESULTS->{discard_B}->{3} = 0;
	$SUMMARY_RESULTS->{discard_C}->{1} = 0;
	$SUMMARY_RESULTS->{discard_C}->{2} = 0;
	$SUMMARY_RESULTS->{discard_C}->{3} = 0;
	$SUMMARY_RESULTS->{discard_D}->{1} = 0;
	$SUMMARY_RESULTS->{discard_D}->{2} = 0;
	$SUMMARY_RESULTS->{discard_D}->{3} = 0;
	$SUMMARY_RESULTS->{discard_E}->{1} = 0;
	$SUMMARY_RESULTS->{discard_E}->{2} = 0;
	$SUMMARY_RESULTS->{discard_E}->{3} = 0;
	$SUMMARY_RESULTS->{discard_F}->{1} = 0;
	$SUMMARY_RESULTS->{discard_F}->{2} = 0;
	$SUMMARY_RESULTS->{discard_F}->{3} = 0;
	
	# Sort clusters + by sum coefficient
	@CLUSTERS_POS = sort { $a->[3] <=> $b->[3] } @CLUSTERS_POS;
	
	# Sort clusters - by sum coefficient
	@CLUSTERS_NEG = sort { $a->[3] <=> $b->[3] } @CLUSTERS_NEG;
	
	CLUST_POS: for (my $p=0; $p<=$#CLUSTERS_POS; $p++) {
	
		my $n=0;
		CLUST_NEG: while ($n <= $#CLUSTERS_NEG) {
		
			# Compare to see if they are mates
			my $temp_findmates = findmates($CLUSTERS_POS[$p], $CLUSTERS_NEG[$n]);
			
			if ($temp_findmates eq 'splice') {
				
				# if  $CLUSTERS_NEG[$n]  has no mates --> predict BPs only -
								
				if (($ALLOW_SINGLE_CLUSTER_INVERSIONS eq 'yes') && ($CLUSTERS_NEG[$n]->[9] == 0) && ($CLUSTERS_NEG[$n]->[2] >= $MIN_SUPPORT_INVERSIONS)) {
				
					$CLUSTERS_NEG[$n]->[9]++;
									
					my($breakpoints) = defineBPs_12neg($CLUSTERS_NEG[$n]);
					
					if (($BP_DEFINITION eq 'wide') or ($BP_DEFINITION eq 'all')) {
						printBPs(filehandles('INV', $chromosome, 'BP2'), $chromosome, $breakpoints, 'NA', 0, 0, 0, '', 'NA', $CLUSTERS_NEG[$n]->[10], $CLUSTERS_NEG[$n]->[1], $CLUSTERS_NEG[$n]->[2], $CLUSTERS_NEG[$n]->[11], $CLUSTERS_NEG[$n]->[12], $CLUSTERS_NEG[$n]->[9]);
					}
					
					if (($BP_DEFINITION eq 'narrow') or ($BP_DEFINITION eq 'all')) {
					
						($breakpoints) = defineBPs_3neg($CLUSTERS_NEG[$n], $breakpoints, $chromosome);
						
						printBPs(filehandles('INV', $chromosome, 'BP3'), $chromosome, $breakpoints, 'NA', 0, 0, 0, '', 'NA',$CLUSTERS_NEG[$n]->[10], $CLUSTERS_NEG[$n]->[1], $CLUSTERS_NEG[$n]->[2], $CLUSTERS_NEG[$n]->[11], $CLUSTERS_NEG[$n]->[12], $CLUSTERS_NEG[$n]->[9]);
					}
					
					$SUMMARY_RESULTS->{inversions}->{'-'}++;
					
				}
				
				# splice $CLUSTERS_NEG[$n]
				splice(@CLUSTERS_NEG, $n, 1);
				
				# try next one...
				next CLUST_NEG;
								
			} elsif ($temp_findmates eq 'last') {
			
				last CLUST_NEG;
			
			} elsif ($temp_findmates eq 'notsatisfyrules') {
			
				# add one and check next...
				$n++;
				next CLUST_NEG;
			
			} else {
			
				# they are pairs!!! 
				if (($CLUSTERS_POS[$p]->[2] + $CLUSTERS_NEG[$n]->[2]) >= $MIN_SUPPORT_INVERSIONS) {

					# add 1 to each "pairs" variable
					$CLUSTERS_POS[$p]->[9]++;
					$CLUSTERS_NEG[$n]->[9]++;
												
					# calculate BPs - DEFINITION 1
					my($breakpoints) = defineBPs_1both($CLUSTERS_POS[$p], $CLUSTERS_NEG[$n]);
				
					# calculate BPs - DEFINITION 2
					($breakpoints) = defineBPs_2both($CLUSTERS_POS[$p], $CLUSTERS_NEG[$n], $breakpoints);
				
					if (($BP_DEFINITION eq 'wide') or ($BP_DEFINITION eq 'all')) {
				
						printBPs(filehandles('INV', $chromosome, 'BP2'), $chromosome, $breakpoints, $CLUSTERS_POS[$p]->[10], $CLUSTERS_POS[$p]->[1], $CLUSTERS_POS[$p]->[2], $CLUSTERS_POS[$p]->[11], $CLUSTERS_POS[$p]->[12], $CLUSTERS_POS[$p]->[9], $CLUSTERS_NEG[$n]->[10], $CLUSTERS_NEG[$n]->[1], $CLUSTERS_NEG[$n]->[2], $CLUSTERS_NEG[$n]->[11], $CLUSTERS_NEG[$n]->[12], $CLUSTERS_NEG[$n]->[9]);
					}
								
					# calculate BPs - DEFINITION 3
					if (($BP_DEFINITION eq 'narrow') or ($BP_DEFINITION eq 'all')) {
				
						($breakpoints) = defineBPs_3pos($CLUSTERS_POS[$p], $breakpoints, $chromosome);
						($breakpoints) = defineBPs_3neg($CLUSTERS_NEG[$n], $breakpoints, $chromosome);
					
						printBPs(filehandles('INV', $chromosome, 'BP3'), $chromosome, $breakpoints, $CLUSTERS_POS[$p]->[10], $CLUSTERS_POS[$p]->[1], $CLUSTERS_POS[$p]->[2], $CLUSTERS_POS[$p]->[11], $CLUSTERS_POS[$p]->[12], $CLUSTERS_POS[$p]->[9], $CLUSTERS_NEG[$n]->[10], $CLUSTERS_NEG[$n]->[1], $CLUSTERS_NEG[$n]->[2], $CLUSTERS_NEG[$n]->[11], $CLUSTERS_NEG[$n]->[12], $CLUSTERS_NEG[$n]->[9]);	
					}
				
					$SUMMARY_RESULTS->{inversions}->{both}++;
				
				}
				
				# add one and check next...
				$n++;
				next CLUST_NEG;
			
			}
		
		}
		
		# if  $CLUSTERS_POS[$p]  has no mates --> predict BPs only +
		if (($ALLOW_SINGLE_CLUSTER_INVERSIONS eq 'yes') && ($CLUSTERS_POS[$p]->[9] == 0) && ($CLUSTERS_POS[$p]->[2] >= $MIN_SUPPORT_INVERSIONS)) {
		
			$CLUSTERS_POS[$p]->[9]++;
						
			my($breakpoints) = defineBPs_12pos($CLUSTERS_POS[$p]);

			if (($BP_DEFINITION eq 'wide') or ($BP_DEFINITION eq 'all')) {
			
				printBPs(filehandles('INV', $chromosome, 'BP2'), $chromosome, $breakpoints, $CLUSTERS_POS[$p]->[10], $CLUSTERS_POS[$p]->[1], $CLUSTERS_POS[$p]->[2], $CLUSTERS_POS[$p]->[11], $CLUSTERS_POS[$p]->[12], $CLUSTERS_POS[$p]->[9], 'NA', 0, 0, 0, '', 'NA');
			}
		
			if (($BP_DEFINITION eq 'narrow') or ($BP_DEFINITION eq 'all')) {
			
				($breakpoints) = defineBPs_3pos($CLUSTERS_POS[$p], $breakpoints, $chromosome);
				
				printBPs(filehandles('INV', $chromosome, 'BP3'), $chromosome, $breakpoints, $CLUSTERS_POS[$p]->[10], $CLUSTERS_POS[$p]->[1], $CLUSTERS_POS[$p]->[2], $CLUSTERS_POS[$p]->[11], $CLUSTERS_POS[$p]->[12], $CLUSTERS_POS[$p]->[9], 'NA', 0, 0, 0, '', 'NA');
			}
			
			$SUMMARY_RESULTS->{inversions}->{'+'}++;
		}
	
	}
	
	# Some negatives could not be analyzed at the end! (if they received a last or notsatisfyrules for the last positive clusters
	# => create inversions with these now!!!
	
	for (my $n=0; $n<=$#CLUSTERS_NEG; $n++) {
	
		if (($ALLOW_SINGLE_CLUSTER_INVERSIONS eq 'yes') && ($CLUSTERS_NEG[$n]->[9] == 0) && ($CLUSTERS_NEG[$n]->[2] >= $MIN_SUPPORT_INVERSIONS)) {
		
			$CLUSTERS_NEG[$n]->[9]++;
							
			my($breakpoints) = defineBPs_12neg($CLUSTERS_NEG[$n]);
			
			if (($BP_DEFINITION eq 'wide') or ($BP_DEFINITION eq 'all')) {
			
				printBPs(filehandles('INV', $chromosome, 'BP2'), $chromosome, $breakpoints, 'NA', 0, 0, 0, '', 'NA', $CLUSTERS_NEG[$n]->[10], $CLUSTERS_NEG[$n]->[1], $CLUSTERS_NEG[$n]->[2], $CLUSTERS_NEG[$n]->[11], $CLUSTERS_NEG[$n]->[12], $CLUSTERS_NEG[$n]->[9]);
			}
			
			if (($BP_DEFINITION eq 'narrow') or ($BP_DEFINITION eq 'all')) {
			
				($breakpoints) = defineBPs_3neg($CLUSTERS_NEG[$n], $breakpoints, $chromosome);
				
				printBPs(filehandles('INV', $chromosome, 'BP3'), $chromosome, $breakpoints, 'NA', 0, 0, 0, '', 'NA', $CLUSTERS_NEG[$n]->[10], $CLUSTERS_NEG[$n]->[1], $CLUSTERS_NEG[$n]->[2], $CLUSTERS_NEG[$n]->[11], $CLUSTERS_NEG[$n]->[12], $CLUSTERS_NEG[$n]->[9]);
			}
			
			$SUMMARY_RESULTS->{inversions}->{'-'}++;
		}	
	
	}	
		
}

sub findmates {

	my($positive, $negative) = @_;
	
	if (($positive->[3] > $negative->[3]) or (abs($positive->[3]-$negative->[3]) < 2*$DATA_STAT{min})) {
	
		return('splice');
	
	} elsif (abs($positive->[3]-$negative->[3]) > 2*$DATA_STAT{max}) {
	
		return('last');
	
	} elsif ( (abs($positive->[5]-$negative->[6]) <= 2*$DATA_STAT{max}) &&     	# Other rules appart from sums
		  (abs($positive->[7]-$negative->[8]) <= 2*$DATA_STAT{max}) && 
		  ($positive->[8] <= $negative->[7]) && 
		  ($negative->[5] >= $positive->[6]) ) {
		  
		if ($JOINPOSNEGCLUSTERS_STRICT eq 'yes') {
		
			# Check new rule here! Overlap of confidence intervals of POS & NEG clusters ##############
			my $posTop = $positive->[3] + ($DATA_STAT{mean} + $DATA_STAT{errorAboveAvg}/sqrt($positive->[1]));
			my $posBottom = $positive->[3] + ($DATA_STAT{mean} - $DATA_STAT{errorBelowAvg}/sqrt($positive->[1]));
			my $negBottom = $negative->[3] - ($DATA_STAT{mean} + $DATA_STAT{errorAboveAvg}/sqrt($negative->[1]));
			my $negTop = $negative->[3] - ($DATA_STAT{mean} - $DATA_STAT{errorBelowAvg}/sqrt($negative->[1]));
		
			if ((($posTop > $negBottom) and ($posTop < $negTop)) or (($posBottom > $negBottom) and ($posBottom < $negTop)) or 
			(($negTop > $posBottom) and ($negTop < $posTop)) or (($negBottom > $posBottom) and ($negBottom < $posTop))) {
		
				return('mates');
		
			} else {
		
				return('notsatisfyrules');
		
			}
			############################################################################################
		
		} else {
		
			# Before new rule was implemented, 'mates' was always returned in for condition
			return('mates');
		
		}
	
	} else {
	
		return('notsatisfyrules');
	
	}

}

sub defineBPs_1both {

	my($positive, $negative) = @_;
	my $breakpoints;
	
	if ($positive->[7] < $negative->[5]) {
		$breakpoints->{BP1end} = $positive->[13]->[5]; ### changed from $positive->[7]
	} else {
		$breakpoints->{BP1end} = $negative->[5];
	}
	
	if ($positive->[8] > $negative->[6]) {
		$breakpoints->{BP2start} = $positive->[8];
	} else {
		$breakpoints->{BP2start} = $negative->[13]->[2]; ### changed from $negative->[6]
	}
	
	$breakpoints->{BP1start} = $positive->[6];
	
	$breakpoints->{BP2end} = $negative->[7];
	
	return($breakpoints);

}

sub defineBPs_2both {

	my($positive, $negative, $breakpoints) = @_;
	
	if ($breakpoints->{BP1start} < ($negative->[6] - $DATA_STAT{max})) {
		$breakpoints->{BP1start} = $negative->[6] - $DATA_STAT{max};
	}
	
	if ($breakpoints->{BP1end} > ($positive->[5] + $DATA_STAT{max})) {
		$breakpoints->{BP1end} = $positive->[5] + $DATA_STAT{max};
	}
	
	if ($breakpoints->{BP2start} < ($negative->[8] - $DATA_STAT{max})) {
		$breakpoints->{BP2start} = $negative->[8] - $DATA_STAT{max};
	}
	
	if ($breakpoints->{BP2end} > ($positive->[7] + $DATA_STAT{max})) {
		$breakpoints->{BP2end} = $positive->[7] + $DATA_STAT{max};
	}
	
	return($breakpoints);

}

sub defineBPs_12pos {

	my($positive) = @_;
	my $breakpoints;
	
	$breakpoints->{BP1start} = $positive->[6];
	
	if ($positive->[7] < ($positive->[5] + $DATA_STAT{max})) {
		$breakpoints->{BP1end} = $positive->[13]->[5]; ### changed from $positive->[7]
	} else {
		$breakpoints->{BP1end} = $positive->[5] + $DATA_STAT{max};
	}
	
	$breakpoints->{BP2start} = $positive->[8];
	
	$breakpoints->{BP2end} = $positive->[7] + $DATA_STAT{max};
	
	return($breakpoints);

}

sub defineBPs_12neg {

	my($negative) = @_;
	my $breakpoints;
	
	$breakpoints->{BP1start} = $negative->[6] - $DATA_STAT{max};
	
	$breakpoints->{BP1end} = $negative->[5];
	
	if ($negative->[6] > ($negative->[8] - $DATA_STAT{max})) {
		$breakpoints->{BP2start} = $negative->[13]->[2]; ### changed from $negative->[6]
	} else {
		$breakpoints->{BP2start} = $negative->[8] - $DATA_STAT{max};
	}
	
	$breakpoints->{BP2end} = $negative->[7];
	
	return($breakpoints);

}

sub defineBPs_3pos {

	my($positive, $breakpoints, $chromosome) = @_;

	foreach my $pemincluster (@{ $positive->[0] }) {

		# Simplification of the rule: [5]+(MIN-([5]-[3])) = [5]+MIN-[5]+[3] = MIN+[3]
		if ( ($DATA_STAT{min} + $ESP_INFO{$chromosome.'+'}->[$pemincluster]->[3]) > $breakpoints->{BP2start}) {
			$breakpoints->{BP2start} = $DATA_STAT{min} + $ESP_INFO{$chromosome.'+'}->[$pemincluster]->[3];
		} 
		
		if ( ($ESP_INFO{$chromosome.'+'}->[$pemincluster]->[5] + ($DATA_STAT{max} - ($breakpoints->{BP1start} - $ESP_INFO{$chromosome.'+'}->[$pemincluster]->[3]))) < $breakpoints->{BP2end}) {
			$breakpoints->{BP2end} = $ESP_INFO{$chromosome.'+'}->[$pemincluster]->[5] + ($DATA_STAT{max} - ($breakpoints->{BP1start} - $ESP_INFO{$chromosome.'+'}->[$pemincluster]->[3]));
		}	
	}

	return($breakpoints);

}

sub defineBPs_3neg {

	my($negative, $breakpoints, $chromosome) = @_;
	
	foreach my $pemincluster (@{ $negative->[0] }) {
	
		if ( ($ESP_INFO{$chromosome.'-'}->[$pemincluster]->[4] - ($DATA_STAT{max} - ($ESP_INFO{$chromosome.'-'}->[$pemincluster]->[6] - $breakpoints->{BP2end}))) > $breakpoints->{BP1start} ) {
			$breakpoints->{BP1start} = $ESP_INFO{$chromosome.'-'}->[$pemincluster]->[4] - ($DATA_STAT{max} - ($ESP_INFO{$chromosome.'-'}->[$pemincluster]->[6] - $breakpoints->{BP2end}));
		}
		
		# Simplification of the rule: [4]-(MIN-([6]-[4])) = [4]-(MIN-[6]+[4]) = [4]-MIN+[6]-[4] = [6]-MIN
		if ( ($ESP_INFO{$chromosome.'-'}->[$pemincluster]->[6] - $DATA_STAT{min}) < $breakpoints->{BP1end}) {
			$breakpoints->{BP1end} = $ESP_INFO{$chromosome.'-'}->[$pemincluster]->[6] - $DATA_STAT{min};
		}
	}
	
	return($breakpoints);

}


sub repaire_inconsistences {

	my($breakpoints, $chromosome) = @_;
	
	# Repare inconsistences
	if (exists $CHR_COORDS->{$ASSEMBLY}) {
		#    Chromosome name
		unless (exists $CHR_COORDS->{$ASSEMBLY}->{$chromosome}) {
			return($breakpoints, 'discard_A');
		}
	
		#    End coordinate
		if ($breakpoints->{BP2end} > $CHR_COORDS->{$ASSEMBLY}->{$chromosome}) {
			if ($breakpoints->{BP2start} > $CHR_COORDS->{$ASSEMBLY}->{$chromosome}) {
				return($breakpoints, 'discard_C');
			} else {
				$breakpoints->{BP2end} = $CHR_COORDS->{$ASSEMBLY}->{$chromosome};
			}
		}
	}
	
	#    Start coordinate $$$$$$$
	if ($breakpoints->{BP1start} < 1) {
		if ($breakpoints->{BP1end} < 1) {
			return($breakpoints, 'discard_B');
		} else {
			$breakpoints->{BP1start} = 1;
		}
	}

	#    BP1start > BP1end $$$$$$$
	if ($breakpoints->{BP1start} > $breakpoints->{BP1end}) {
		return($breakpoints, 'discard_D');
	}
	
	#    BP2start > BP2end $$$$$$$
	if ($breakpoints->{BP2start} > $breakpoints->{BP2end}) {
		return($breakpoints, 'discard_E');
	}

	#    BP1end > BP2start $$$$$$$
	if ($breakpoints->{BP1end} > $breakpoints->{BP2start}) {
		return($breakpoints, 'discard_F');
	}

	return($breakpoints, '');

}

sub printBPs {

	my($fh, $chr, $breakpoints, $pos, $pos_support, $pos_wsupport, $pos_uniquemaps, $pos_tags, $pos_combinations, $neg, $neg_support, $neg_wsupport, $neg_uniquemaps, $neg_tags, $neg_combinations) = @_;
	
	my $discard;
	
	($breakpoints, $discard) = repaire_inconsistences($breakpoints, $chr);
	
	my $temp_alsoprint = '';
	
	if ($discard =~ /^discard/) { 
	
		my($bpdef) = ($fh =~ /(\d)$/);
		$SUMMARY_RESULTS->{$discard}->{$bpdef}++;
		
		if ($TAG_INCONGRUENT_BPS eq 'yes') {
			$temp_alsoprint .= '!';
		}
		
	}
	
	if (($TAG_MULTIPLE_USE_OF_CLUSTERS eq 'yes') and (($pos_combinations>1) or ($neg_combinations>1))) {
		$temp_alsoprint .= '#';
	}
	
	if (($TAG_MAPPINGS_IN_REPEATS ne 'no') and (($pos_tags =~ /\^/) or ($neg_tags =~ /\^/))) {
		$temp_alsoprint .= '^';
	}
	
	if (($TAG_MAPPINGS_IN_SEGDUPS ne 'no') and (($pos_tags =~ /:/) or ($neg_tags =~ /:/))) {
		$temp_alsoprint .= ':';
	}
	
	# Sometimes support/weighted support contains decimals => round to second decimal position!
	if ($pos_support =~ /\d+\.\d+/) {
		$pos_support = sprintf("%.2f", $pos_support);
	}

	if ($neg_support =~ /\d+\.\d+/) {
		$neg_support = sprintf("%.2f", $neg_support);
	}


	if ($pos_wsupport =~ /\d+\.\d+/) {
		$pos_wsupport = sprintf("%.2f", $pos_wsupport);
	}

	if ($neg_wsupport =~ /\d+\.\d+/) {
		$neg_wsupport = sprintf("%.2f", $neg_wsupport);
	}

	printf $fh "$pos\t$neg\t$chr\t".($pos_wsupport+$neg_wsupport)."\t%.0f\t%.0f\t%.0f\t%.0f\t$pos_wsupport($pos_support)/$neg_wsupport($neg_support)\t$temp_alsoprint\n", $breakpoints->{BP1start}, $breakpoints->{BP1end}, $breakpoints->{BP2start}, $breakpoints->{BP2end};

}

sub writesummaryresults {

	my($chromosome) = @_;
	
	my $pems_pos = scalar(@{$ESP_INFO{$chromosome.'+'}});
	my $pems_neg = scalar(@{$ESP_INFO{$chromosome.'-'}});
	
	print SUMMARYRESULTS $chromosome."\t".$pems_pos.'/'.$pems_neg."\t".
	$SUMMARY_RESULTS->{clusters}->{'+'}.'/'.$SUMMARY_RESULTS->{clusters}->{'-'}."\t".
	$SUMMARY_RESULTS->{clusters_neededcorrection}->{'+'}.'/'.$SUMMARY_RESULTS->{clusters_neededcorrection}->{'-'}."\t".
	$SUMMARY_RESULTS->{inversions}->{both}.'/'.$SUMMARY_RESULTS->{inversions}->{'+'}.'/'.$SUMMARY_RESULTS->{inversions}->{'-'}."\t".
	$SUMMARY_RESULTS->{discard_A}->{1}.','.$SUMMARY_RESULTS->{discard_A}->{2}.','.$SUMMARY_RESULTS->{discard_A}->{3}.'/'.
	$SUMMARY_RESULTS->{discard_B}->{1}.','.$SUMMARY_RESULTS->{discard_B}->{2}.','.$SUMMARY_RESULTS->{discard_B}->{3}.'/'.
	$SUMMARY_RESULTS->{discard_C}->{1}.','.$SUMMARY_RESULTS->{discard_C}->{2}.','.$SUMMARY_RESULTS->{discard_C}->{3}.'/'.
	$SUMMARY_RESULTS->{discard_D}->{1}.','.$SUMMARY_RESULTS->{discard_D}->{2}.','.$SUMMARY_RESULTS->{discard_D}->{3}.'/'.
	$SUMMARY_RESULTS->{discard_E}->{1}.','.$SUMMARY_RESULTS->{discard_E}->{2}.','.$SUMMARY_RESULTS->{discard_E}->{3}.'/'.
	$SUMMARY_RESULTS->{discard_F}->{1}.','.$SUMMARY_RESULTS->{discard_F}->{2}.','.$SUMMARY_RESULTS->{discard_F}->{3}."\t".
	localtime( ) . "\n";
	
	print "$chromosome	PEMs: $pems_pos/$pems_neg, clusters: ".$SUMMARY_RESULTS->{clusters}->{'+'}."/".$SUMMARY_RESULTS->{clusters}->{'-'}.
	", inversions: ".$SUMMARY_RESULTS->{inversions}->{both}.'/'.$SUMMARY_RESULTS->{inversions}->{'+'}.'/'.$SUMMARY_RESULTS->{inversions}->{'-'}.
	"\n";

}

