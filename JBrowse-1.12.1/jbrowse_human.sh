#!/bin/bash

##### jbrowse_human.sh

#### step by step jbrowse configuration!!!

set -e 


###################################
### REFERENCE SEQUENCE

bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr1.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr2.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr3.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr4.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr5.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr6.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr7.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr8.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr9.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr10.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr11.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr12.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr13.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr14.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr15.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr16.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr17.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr18.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr19.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr20.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr21.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chr22.fa && bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chrX.fa &&  bin/prepare-refseqs.pl --fasta /home/rgomez/jbrowse/JBROWSE/human/chrY.fa 

sed -i -e 's/"DNA",/"DNA",\
	"metadata" : {"general_tracks":"Reference Sequence"},/g' data/trackList.json

###############################
#### GENES

# bin/ucsc-to-json.pl --in files/ucsc --track knownGene

bin/flatfile-to-json.pl --trackType CanvasFeatures --trackLabel gene_annotations --autocomplete all --gff files/ucsc/genes.gff3 --key "Gene annotations" --metadata '{"general_tracks":"Gene annotations"}' --config '{"menuTemplate" : [{"label":"View details", "title": "{type} {name}", "action":"contentDialog", "iconClass": "dijitIconTask"},{"iconClass":"dijitIconFilter"},{"label":"Search for {name} at NCBI", "title": "Searching for {name} at NCBI", "iconClass":"dijitIconDatabase","action": "iframeDialog","url":"http://www.ncbi.nlm.nih.gov/gquery/?term={name}"},{"label":"Search for {name} at UCSC genes", "title": "Searching for {name} at UCSC genes", "iconClass":"dijitIconDatabase","action": "iframeDialog","url":"https://genome.ucsc.edu/cgi-bin/hgGene?hgg_gene={id}&db=hg19"}]}'




