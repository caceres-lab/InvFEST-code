from Bio import SeqIO
import sys
print sys.argv[1]
outfile = sys.argv[1].replace(".gbk",".tab")
out = open(outfile,'w')
for record in SeqIO.parse(open(sys.argv[1], "rU"), "genbank") :
	for feature in record.features:
		ftype = feature.type
		start = feature.location.start.position
		stop = feature.location.end.position
		try:
			name = feature.qualifiers['label'][0]
		except:
			# some features only have a locus tag
			name = feature.qualifiers
			#rint name
			name="."
		if feature.strand < 0:
			strand = "-"
		else:
			strand = "+"
		out.write("%s\t%s\t%s\t%s\t%s\n" % (ftype,name,start,stop,strand))
		#print "%s %s %s %s %s\n" % (ftype,name,start,stop,strand)
out.close()
