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
	reg1 int(11) DEFAULT '0',
	host mediumtext NOT NULL,
	file mediumtext NOT NULL,
	uri mediumtext NOT NULL,
	isdirty tinyint(1) DEFAULT '0',
	ismarkedtodelete tinyint(1) DEFAULT '0',
	additionalhash varchar(40) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY pid (pid),
) ENGINE=InnoDB;