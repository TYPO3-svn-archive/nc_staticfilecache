#  
# Table structure for table 'pages'  
#  
CREATE TABLE pages (  
	tx_ncstaticfilecache_cache tinyint(1) DEFAULT '1',  
);  

#
# Table structure for table 'tx_ncstaticfilecache_file'
#
CREATE TABLE tx_ncstaticfilecache_file (
	uid int(11) NOT NULL auto_increment,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cache_timeout int(11) DEFAULT '0' NOT NULL,
	explanation mediumtext,
	pid int(11) DEFAULT '0',
	host mediumtext NOT NULL,
	file mediumtext NOT NULL,
	
	PRIMARY KEY (uid),
);