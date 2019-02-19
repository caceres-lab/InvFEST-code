cd $1

liftOver -positions $2.in hg19ToHg18.over.chain.gz $2.out liftOver.unmapped

rm *bed*
