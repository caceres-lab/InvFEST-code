#!/usr/bin/perl

#/home/invfest/perl5/bin/perl

use lib '/home/invfest/perl5/lib/perl5';
use strict;
use Bio::Graphics;
use Bio::SeqFeature::Generic;
use Bio::Graphics::Panel;
use Bio::Graphics::Feature;
use CGI qw(:standard);


#GET URL parameters
my $q     = new CGI;
my $id    = $q->param('id');

my $bp_bp1s       = $q->param('bp_bp1s');
my $bp_bp1e       = $q->param('bp_bp1e');
my $bp_bp2s       = $q->param('bp_bp2s');
my $bp_bp2e       = $q->param('bp_bp2e');

my $a_bp1s       = $q->param('a_bp1s');
my $a_bp1e       = $q->param('a_bp1e');
my $a_bp2s       = $q->param('a_bp2s');
my $a_bp2e       = $q->param('a_bp2e');

my $g_bp1s       = $q->param('g_bp1s');
my $g_bp1e       = $q->param('g_bp1e');
my $g_bp2s       = $q->param('g_bp2s');
my $g_bp2e       = $q->param('g_bp2e');

my $k_bp1s       = $q->param('k_bp1s');
my $k_bp1e       = $q->param('k_bp1e');
my $k_bp2s       = $q->param('k_bp2s');
my $k_bp2e       = $q->param('k_bp2e');

my $l_bp1s       = $q->param('l_bp1s');
my $l_bp1e       = $q->param('l_bp1e');
my $l_bp2s       = $q->param('l_bp2s');
my $l_bp2e       = $q->param('l_bp2e');

my $m_bp1s       = $q->param('m_bp1s');
my $m_bp1e       = $q->param('m_bp1e');
my $m_bp2s       = $q->param('m_bp2s');
my $m_bp2e       = $q->param('m_bp2e');

my $w_bp1s       = $q->param('w_bp1s');
my $w_bp1e       = $q->param('w_bp1e');
my $w_bp2s       = $q->param('w_bp2s');
my $w_bp2e       = $q->param('w_bp2e');


my $bp_id       = $q->param('bp_id');
my $a_id       = $q->param('a_id');
my $g_id       = $q->param('g_id');
my $k_id       = $q->param('k_id');
my $l_id       = $q->param('l_id');
my $m_id       = $q->param('m_id');
my $w_id       = $q->param('w_id');

my $all_bp1s       = $q->param('all_bp1s');
my $all_bp1e       = $q->param('all_bp1e');
my $all_bp2s       = $q->param('all_bp2s');
my $all_bp2e       = $q->param('all_bp2e');
my $all_name       = $q->param('all_name');
my $all_id       = $q->param('all_id');

my $chr = $q->param('chr');

my @bp_id_ar       	= split (/:/,$bp_id) ; 
my @a_id_ar       	= split (/:/,$a_id) ; 
my @g_id_ar       	= split (/:/,$g_id) ; 
my @k_id_ar       	= split (/:/,$k_id) ; 
my @l_id_ar       	= split (/:/,$l_id) ; 
my @m_id_ar       	= split (/:/,$m_id) ; 
my @w_id_ar       	= split (/:/,$w_id) ; 

my @bp_bp1s_ar       	= split (/:/,$bp_bp1s) ;      
my @bp_bp1e_ar       	= split (/:/,$bp_bp1e) ;      
my @bp_bp2s_ar       	= split (/:/,$bp_bp2s);      
my @bp_bp2e_ar       	= split (/:/,$bp_bp2e);      

my @a_bp1s_ar       	= split (/:/,$a_bp1s) ;      
my @a_bp1e_ar       	= split (/:/,$a_bp1e) ;      
my @a_bp2s_ar       	= split (/:/,$a_bp2s);      
my @a_bp2e_ar       	= split (/:/,$a_bp2e);      

my @g_bp1s_ar       	= split (/:/,$g_bp1s) ;      
my @g_bp1e_ar       	= split (/:/,$g_bp1e) ;      
my @g_bp2s_ar       	= split (/:/,$g_bp2s);      
my @g_bp2e_ar       	= split (/:/,$g_bp2e);      

my @k_bp1s_ar       	= split (/:/,$k_bp1s) ;      
my @k_bp1e_ar       	= split (/:/,$k_bp1e) ;      
my @k_bp2s_ar       	= split (/:/,$k_bp2s);      
my @k_bp2e_ar       	= split (/:/,$k_bp2e);      

my @l_bp1s_ar       	= split (/:/,$l_bp1s) ;      
my @l_bp1e_ar       	= split (/:/,$l_bp1e) ;      
my @l_bp2s_ar       	= split (/:/,$l_bp2s);      
my @l_bp2e_ar       	= split (/:/,$l_bp2e);      

my @m_bp1s_ar       	= split (/:/,$m_bp1s) ;      
my @m_bp1e_ar       	= split (/:/,$m_bp1e) ;      
my @m_bp2s_ar       	= split (/:/,$m_bp2s);      
my @m_bp2e_ar       	= split (/:/,$m_bp2e);      

my @w_bp1s_ar       	= split (/:/,$w_bp1s) ;      
my @w_bp1e_ar       	= split (/:/,$w_bp1e) ;      
my @w_bp2s_ar       	= split (/:/,$w_bp2s);      
my @w_bp2e_ar       	= split (/:/,$w_bp2e);      

my @all_bp1s_ar       	= split (/:/,$all_bp1s) ;      
my @all_bp1e_ar       	= split (/:/,$all_bp1e) ;      
my @all_bp2s_ar       	= split (/:/,$all_bp2s);      
my @all_bp2e_ar       	= split (/:/,$all_bp2e);      
my @all_name_ar       	= split (/:/,$all_name);      
my @all_id_ar       	= split (/:/,$all_id) ; 


###select extreme coordenates
#first coordenate arrays
#my @starts = ($bp_bp1s_ar[0],  $a_bp1s_ar[0],  $g_bp1s_ar[0],  $k_bp1s_ar[0],  $l_bp1s_ar[0],  $m_bp1s_ar[0],  $w_bp1s_ar[0]);
my @starts = @all_bp1s_ar;
push(@starts,$bp_bp1s_ar[0]);
#my @ends   = ($bp_bp2e_ar[0],  $a_bp2e_ar[0],  $g_bp2e_ar[0],  $k_bp2e_ar[0],  $l_bp2e_ar[0],  $m_bp2e_ar[0],  $w_bp2e_ar[0]);
my @ends = @all_bp2e_ar;
push(@ends,$bp_bp2e_ar[0]);

#discard UNDEFined coordenates for descending sort
my @starts_push = ();
foreach (@starts) {
	push(@starts_push, $_) if defined $_;
}

#sort arrays
my @sort_start = sort {$a <=> $b} @starts_push;
my @sort_end = sort {$b <=> $a} @ends;

#select max and min coordenate
my $feat_start =   $sort_start[0];
my $feat_end   =  $sort_end[0];
my $end_start = $feat_end-$feat_start;

my $len   = abs(int($end_start+($end_start*0.6)));
my $start = abs(int($feat_start-($end_start*0.3)));



### Database connection for genes in region

use DBI;
my $dbh = DBI->connect('dbi:mysql:INVFEST-DB-dev', 'invfest', 'pwdInvFEST')
	or die "Error connecting DB: $DBI::errstr\n";


####exons query
#get genes in region first
my $query = "SELECT DISTINCT idHsRefSeqGenes, symbol, strand, txStart, txEnd, exonCount, exonStarts, exonEnds FROM HsRefSeqGenes 
			 WHERE chr = \"".$chr."\" 
			 AND ((txStart BETWEEN ".$start." AND ".($start+$len).")  
			 OR (txEnd BETWEEN ".$start." AND ".($start+$len).") OR ($start BETWEEN txStart AND txEND) OR (($start+$len) BETWEEN txStart AND txEND))";

my $sth  = $dbh->prepare($query);
$sth->execute();
my $rows = $sth->rows();
my @genes = ();
if ($rows >= 1) {
	while ( my $ref = $sth->fetchrow_hashref() ) {
	    push (@genes, $ref);
	}
}
$sth->finish();

=head1
my $zip_string = join q{,}, map $dbh->quote($_), @genes;

# get exons from previous genes
my $query = "SELECT DISTINCT genes.symbol, genes.strand, exons.exonStart, exons.exonEnd 
			 FROM HsRefSeqGenes_exons as exons, HsRefSeqGenes as genes 
			 WHERE exons.idHsRefSeqGenes = genes.idHsRefSeqGenes 
			 AND genes.symbol IN (".$zip_string.")";

my $sth  = $dbh->prepare($query);
$sth->execute();
my $rows = $sth->rows();
my @exons = ();
if ($rows >= 1) {
	while ( my $ref = $sth->fetchrow_hashref() ) {
	    push (@exons, $ref);
	}
}
$sth->finish();
=cut

### Get current inversion's name

my $query = "SELECT name FROM inversions WHERE id = $id;";
my $sth  = $dbh->prepare($query);
$sth->execute();
my $rows = $sth->rows();
my @names = ();
if ($rows >= 1) {
	while ( my $ref1 = $sth->fetchrow_hashref() ) {
	    push (@names, $ref1);
	}
}
my $name;
for my $namess (@names){
	$name = $namess->{'name'};
}
$sth->finish();
#### get segmental duplications
my $query = "SELECT chrom, chromStart, chromEnd, name FROM seg_dups 
			 WHERE chrom = \"".$chr."\" 
			 AND ((chromStart BETWEEN ".$start." AND ".($start+$len).")  
			 OR (chromEnd BETWEEN ".$start." AND ".($start+$len)."))";

my $sth  = $dbh->prepare($query);
$sth->execute();
my $rows = $sth->rows();
my @seg_dups = ();
if ($rows >= 1) {
	while ( my $ref = $sth->fetchrow_hashref() ) {
	    push (@seg_dups, $ref);
	}
}
$sth->finish();

#### get other inversions present in the same inversion
my $query = "SELECT i.name, i.id, b.inv_id, b.id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end FROM inversions i INNER JOIN breakpoints b ON b.id = (SELECT id FROM breakpoints b2 WHERE b2.inv_id=i.id ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC LIMIT 1) WHERE b.chr = \"".$chr."\" AND i.id = b.inv_id AND i.status NOT LIKE 'WITHDRAWN' AND ((b.bp1_start BETWEEN ".$start." AND ".($start+$len).") OR (b.bp2_end BETWEEN ".$start." AND ".($start+$len).") OR ($start BETWEEN b.bp1_start AND b.bp2_end) OR (($start+$len) BETWEEN b.bp1_start AND b.bp2_end));";

my $sth  = $dbh->prepare($query);
$sth->execute();
my $rows = $sth->rows();
my @other_inv = ();
if ($rows >= 1) {
	while ( my $ref = $sth->fetchrow_hashref() ) {
	    push (@other_inv, $ref);
	}
}
$sth->finish();

$dbh->disconnect();
#### End database

=head
### DAS
use Bio::Das;

my $das = Bio::Das->new(-source => 'http://genome.ucsc.edu/cgi-bin/das',
					   -dsn    => 'hg18',
					   -agreggator => 'refGene',
					   );
my $segment  = $das->segment('1:'.$start.','.$len);
my @features = $segment->features;
=cut



######Create tracks
#Create Panel
my $panel = Bio::Graphics::Panel->new(
                                      -length    => $len,
                                      -width     => 750,
                                      -pad_left  => 10,
                                      -pad_right => 10,
                                      -start     => $start,
                                      -stop      => $start+$len,
                                      -key_style => 'between',
                                      -spacing   => 10,
                                      -grid      => 1,
                                     );

#Create Rule with arrows
my $full_length = Bio::SeqFeature::Generic->new(
                                                -start => $start,
                                                -end   => $start+$len,
                                               );
$panel->add_track($full_length,
                  -glyph   => 'arrow',
                  -tick    => 2,
                  -fgcolor => 'black',
                  -double  => 1,
                  -label => $chr,
                 );
 
#ADD Tracks
addFeatureGenes(\@genes, "blue", "segments", $start, ($start+$len));
addFeature2coord("Segmental duplications", \@seg_dups, "green", "segments");
addFeatureotherinversions("Inversion breakpoints", \@other_inv, 1000, "segments");
#addFeature4coord("Breakpoints",      \@bp_bp1s_ar, \@bp_bp2s_ar, \@bp_bp1e_ar, \@bp_bp2e_ar, \@bp_id_ar, 1000, "darkorchid");
addFeatureLine( "", 0, 0, "green", "line"); #blank track
#addFeatureLine( "", $start, ($start+$len), "green", "line");
#addFeatureLine( "", 0, 0, "green", "line"); #blank track
addFeatureLine( "PREDICTIONS for $name", 0, 0, "green", "line"); #blank track

#addFeature4coord("Ahn evidence",     \@a_bp1s_ar,  \@a_bp2s_ar,  \@a_bp1e_ar,  \@a_bp2e_ar,  \@a_id_ar, 800,  "peachpuff");
#addFeature4coord("GRIAL evidence",   \@g_bp1s_ar,  \@g_bp2s_ar,  \@g_bp1e_ar,  \@g_bp2e_ar,  \@g_id_ar, 300,  "goldenrod");
#addFeature4coord("Korbel evidence",  \@k_bp1s_ar,  \@k_bp2s_ar,  \@k_bp1e_ar,  \@k_bp2e_ar,  \@k_id_ar, 400,  "darkslategray");
#addFeature4coord("Levy evidence ",   \@l_bp1s_ar,  \@l_bp2s_ar,  \@l_bp1e_ar,  \@l_bp2e_ar,  \@l_id_ar, 200,  "cornflowerblue");
#addFeature4coord("McKernan evidence",\@m_bp1s_ar,  \@m_bp2s_ar,  \@m_bp1e_ar,  \@m_bp2e_ar,  \@m_id_ar, 700,  "orange");
#addFeature4coord("Wang evidence",    \@w_bp1s_ar,  \@w_bp2s_ar,  \@w_bp1e_ar,  \@w_bp2e_ar,  \@w_id_ar, 500,  "darkmagenta");
addFeature4coordAll(\@all_name_ar,     \@all_bp1s_ar,  \@all_bp2s_ar,  \@all_bp1e_ar,  \@all_bp2e_ar,  \@all_id_ar, 2000);
print "Content-Type: image/png\n\n";
print $panel->png;


exit;


###############
##### Functions
sub addFeatureotherinversions{
		my($track_name, $ref_features, $score, $color, $glyph) = @_ ;

	
		my @features = @{$ref_features};

		my $newtrack = $panel->add_track(
							  -glyph     => 'segments',
							  -label     => 1,
							  -fgcolor   => sub { 
                                                                            my $feature = shift; 
									    return "black" if $feature->primary_tag eq 'motif1'; 
									    return "black" if $feature->primary_tag eq 'motif2';
 									    return 'black' if $feature->primary_tag eq 'bp1e_bigger_bp2s'
 									},
							  -key => $track_name,
                                  			  -height => 12, 
                                  			  -linewidth => sub { 
                                                                            my $feature = shift; 
									    return '0' if $feature->primary_tag eq 'motif1'; 
									    return '2' if $feature->primary_tag eq 'motif2'
 									},  
							  -connector => 'dashed',
							  -connector_color => "black",
							  -bgcolor => sub { 
                                                                            my $feature = shift; 
									    return 'gray' if $feature->primary_tag eq 'motif1'; 
									    return 'darkorchid' if $feature->primary_tag eq 'motif2';
									    return 'darkorchid' if $feature->primary_tag eq 'bp1e_bigger_bp2s'
 									}  
                                
							 );
		for my $feature_data (@features){	
			my $foo = Bio::SeqFeature::Generic->new(
								-display_name => $feature_data->{'name'},
								-score        => $score,
							   );

			if ($feature_data->{'inv_id'} == $id){
				
				if($feature_data->{'bp1_end'} >= $feature_data->{'bp2_start'}){
				my $subfoo3= Bio::SeqFeature::Generic->new(
					-start => $feature_data->{'bp1_start'},
					-end   => $feature_data->{'bp2_end'},
					#-source_tag => "$id",
					-primary=>'bp1e_bigger_bp2s'
				);
		  
				$foo->add_sub_SeqFeature($subfoo3,"EXPAND");


				}
				else{
				my $subfoo3= Bio::SeqFeature::Generic->new(
					-start => $feature_data->{'bp1_start'},
					-end   => $feature_data->{'bp1_end'},
					#-source_tag => "$id",
					-primary=>'motif2'
				);
				my $subfoo4= Bio::SeqFeature::Generic->new(
					-start => $feature_data->{'bp2_start'},
					-end   => $feature_data->{'bp2_end'},
					#-source_tag =>"$id",
					-primary=>'motif2'
				);
		  
				$foo->add_sub_SeqFeature($subfoo3,"EXPAND");
				$foo->add_sub_SeqFeature($subfoo4,"EXPAND");
				}
			}

			else{
			my $subfoo1= Bio::SeqFeature::Generic->new(
					-start => $feature_data->{'bp1_start'},
					-end   => $feature_data->{'bp1_end'},
					-primary=>'motif1'
			);
			my $subfoo2= Bio::SeqFeature::Generic->new(
					-start => $feature_data->{'bp2_start'},
					-end   => $feature_data->{'bp2_end'},
					-primary=>'motif1'
			);
			
			
			$foo->add_sub_SeqFeature($subfoo1,"EXPAND");
			$foo->add_sub_SeqFeature($subfoo2,"EXPAND");
			}

			$newtrack->add_feature($foo);
			
		}
	

 }
sub addFeatureGenes{
	my($refUniqueGenes, $color, $glyph, $Gstart, $Gend) = @_ ;
	
	my @genes = @{$refUniqueGenes};
				
	my $genetrack = $panel->add_track(
							  -glyph     => $glyph,
							  -label     => 1,
							  -stranded => 1,
							  #-strand_arrow => "ends",
							  -connector => "hat",
						          -key => "Genes",
						          -height => 10,
							  -linewidth => 1,
							  -bgcolor   => $color,
							 );

	#single feature 
	for my $gene (@genes) {
		my $nextGene = Bio::SeqFeature::Generic->new(
						-display_name => $gene->{'symbol'},

					   );

		#subtracks
		my @exonStarts = split /,/, $gene->{'exonStarts'};
		my @exonEnds = split /,/, $gene->{'exonEnds'};
		
		for (my $i=0; $i<$gene->{'exonCount'}; $i++) {

			#if (($exonStarts[$i]>=$Gstart) and ($exonEnds[$i]<=$Gend)) {
			
				my $exon= Bio::SeqFeature::Generic->new(
					-start => $exonStarts[$i],
					-end   => $exonEnds[$i],
					-strand => $gene->{'strand'},
				);
				
				$nextGene->add_sub_SeqFeature($exon,"EXPAND");
			#}
		}

		$genetrack->add_feature($nextGene);
	}

}


sub addFeature2coord{
	my($track_name, $ref_features, $color, $glyph) = @_ ;
	
	my @features = @{$ref_features};

	my $newtrack = $panel->add_track(
							  -glyph     => $glyph,
							  -label     => 1,
							  -bgcolor   => $color,
							  -height => 4,
                              -key => $track_name,
                              #-feature_limit => 100,

							 );
	
	for my $feature_data (@features){
		my $feature= Bio::SeqFeature::Generic->new(
			-start => $feature_data->{'chromStart'},
			-end   => $feature_data->{'chromEnd'},
			-display_name => $feature_data->{'name'},
		);
		$newtrack->add_feature($feature,"EXPAND");
	}					 

 }

sub addFeatureLine{
	my($track_name, $start, $stop, $color, $glyph) = @_ ;
	
	my $newtrack = $panel->add_track(
							  -glyph     => $glyph,
							  -label     => 1,
							  -bgcolor   => $color,
							  -height => 1,
                              -key => $track_name,

							 );
	
	my $feature= Bio::SeqFeature::Generic->new(
		-start => $start,
		-end   => $stop,
	);
	$newtrack->add_feature($feature,"EXPAND");		 

 }

# sub addFeature2coord{
# 	my($track_name, $ref_bp1, $ref_bp2, $ref_id, $score, $color) = @_ ;
	
# 	if ( defined $ref_id->[0]) {
				
# 		my $newtrack = $panel->add_track(
# 								  -glyph     => 'segments',
# 								  -label     => 1,
# 								  -bgcolor   => $color,
# 								  -min_score => 0,
# 								  -max_score => 1000,
# 								  -connector => 'dashed',
#                                   -key => $track_name,
# 								 );
								 
# 		for my $i (0..@#{ $ref_id }) {	
# 			my $foo = Bio::SeqFeature::Generic->new(
# 								#-display_name => $track_name." id ".$ref_id->[$i],
# 								-score        => $score,
# 								-start        => $ref_bp1->[$i],
# 								-end          => $ref_bp2->[$i],
# 							   );
# 			$newtrack->add_feature($foo);
			
# 		}
# 	}
#  }


sub addFeature4coord{
	my($track_name, $ref_bp1_start, $ref_bp2_start, $ref_bp1_end, $ref_bp2_end, $ref_id, $score, $color) = @_ ;
	
	if ( defined $ref_id->[0]) {
				
		my $newtrack = $panel->add_track(
								  -glyph     => 'segments',
								  -label     => 1,
								  -bgcolor   => $color,
								  -fgcolor   => $color,
                                  #-tkcolor => '#e0e0e0',
								  -connector => 'dashed',
                                  -key => $track_name,
                                  -height => 12, 
                                  -linewidth => 2,

								 );
		my $max = $#{ $ref_id };		 
		for my $i (0..$max) {	
			my $foo = Bio::SeqFeature::Generic->new(
								#-display_name => $track_name." id ".$ref_id->[$i],
								-score        => $score,
							   );
			my $subfoo1= Bio::SeqFeature::Generic->new(
					-start => $ref_bp1_start->[$i],
					-end   => $ref_bp1_end->[$i]
			);
			my $subfoo2= Bio::SeqFeature::Generic->new(
					-start => $ref_bp2_start->[$i],
					-end   => $ref_bp2_end->[$i]
			);
			$foo->add_sub_SeqFeature($subfoo1,"EXPAND");
			$foo->add_sub_SeqFeature($subfoo2,"EXPAND");
			$newtrack->add_feature($foo);
			
		}
	}
 }

sub addFeature4coordAll{
	my($track_name, $ref_bp1_start, $ref_bp2_start, $ref_bp1_end, $ref_bp2_end, $ref_id, $score) = @_ ;
	#Generate random colors:
	my @colors;
	for (my $i = 0; $i < 64; $i++) {
		my ($rand,$x);
		my @hex;
		for ($x = 0; $x < 3; $x++) {
			$rand = rand(255);
			$hex[$x] = sprintf ("%x", $rand);
			if ($rand < 9) {$hex[$x] = "0" . $hex[$x];}
			if ($rand > 9 && $rand < 16) {$hex[$x] = "0" . $hex[$x];}
		}
		$colors[$i] = "\#" . $hex[0] . $hex[1] . $hex[2];
	}

	if ( defined $ref_id->[0]) {
		my $previousTrack;		
		my $max = $#{ $ref_id };		 
		for my $i (0..$max) {
		
			my $displayName;
			
			if ($track_name->[$i] eq $previousTrack) {
			
				$displayName = ' ';
			
			} else {
			
				$displayName = $track_name->[$i];
			
			}
			
			my $newtrack = $panel->add_track(
								  -glyph     => 'segments',
								  -label     => 1,
								# -bgcolor   => $colors[$i], #random colors
								  -bgcolor   => 'goldenrod',
								  -min_score => 0,
								  -max_score => 1000,
								  -connector => 'dashed',
								  -height    => 5,
        				          		  -key       => $displayName,

								 );

			my $foo = Bio::SeqFeature::Generic->new(
								#-display_name => $track_name." id ".$ref_id->[$i],
								-score        => $score,
							   );
			my $subfoo1= Bio::SeqFeature::Generic->new(
					-start => $ref_bp1_start->[$i],
					-end   => $ref_bp1_end->[$i]
			);
			my $subfoo2= Bio::SeqFeature::Generic->new(
					-start => $ref_bp2_start->[$i],
					-end   => $ref_bp2_end->[$i]
			);
			$foo->add_sub_SeqFeature($subfoo1,"EXPAND");
			$foo->add_sub_SeqFeature($subfoo2,"EXPAND");
			$newtrack->add_feature($foo);
			
			$previousTrack = $track_name->[$i];
			
		}
	}
 }

