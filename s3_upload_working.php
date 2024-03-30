<?php

class S3Uploader{
	private $s3_host;	// ams3.digitaloceanspaces.com
	private $s3_key;
	private $s3_secret;
	private $s3_bucket;

	private $s3_storage_class;
	private $s3_acl;

	const ACL_PRIVATE	= "private";
	const ACL_PUBLIC	= "public-read";

	const SC_STANDARD	= "STANDARD";

	const DEBUG		= false;

	function __construct($s3_host, $s3_key, $s3_secret, $s3_bucket){
		$this->s3_host		= $s3_bucket . "." . $s3_host;
		$this->s3_key		= $s3_key;
		$this->s3_secret	= $s3_secret;
		$this->s3_bucket	= $s3_bucket;

		$this->s3_storage_class	= self::SC_STANDARD;
		$this->s3_acl		= self::ACL_PUBLIC;
	}

	function setStorageClass($s3_storage_class){
		// STANDARD, REDUCED_REDUNDANCY, etc.
		$this->s3_storage_class	= $s3_storage_class;
	}

	function setAcl($acl){
		// "private" or "public-read"
		$this->s3_acl		= $acl;
	}

	function setAclBool($acl){
		if ($acl)
			return setAcl(self::ACL_PUBLIC);
		else
			return setAcl(self::ACL_PRIVATE);
	}

	private static function date(){
		return strftime("%a, %d %b %Y %T %z");
	}

	function uploadFile($src_file, $dst_file, $content_type){
		$date		= $this->date();

		// space after : is ommited intentionally!!!
		$acl		= "x-amz-acl:"			. $this->s3_acl;
		$storage_class	= "x-amz-storage-class:"	. $this->s3_storage_class;

		$string		=	"PUT\n"			.
					"\n"			.
					"$content_type\n"	.
					"$date\n"		.
					"$acl\n"		.
					"$storage_class\n"	.
					"/" . $this->s3_bucket . $dst_file
		;

		$signature = base64_encode(
					hash_hmac("sha1", $string, $this->s3_secret, true)
		);

		$curl_headers = [
			"Host:"			. $this->s3_host		,
			"Date:"			. $date				,
			"Content-Type:"		. $content_type			,
			$acl							,
			$storage_class						,
			"Authorization:"	. "AWS " . $this->s3_key . ":" . $signature
		];

		$curl_url = "https://" . $this->s3_host . $dst_file;

		$file_contents  = file_get_contents($src_file);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL,			$curl_url	);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST,	"PUT"		);
		curl_setopt($curl, CURLOPT_POSTFIELDS,		$file_contents	);
		curl_setopt($curl, CURLOPT_HTTPHEADER,		$curl_headers	);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION,	true		);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,	true		);

		if (self::DEBUG){
		curl_setopt($curl, CURLOPT_HEADER, 		true		);
		curl_setopt($curl, CURLOPT_VERBOSE,		true		);
		}

		$result = curl_exec($curl);

		if ($result === false) {
		    	@curl_close($curl);

			// int is more readable in print_r

			return [
				"ok"		=> 0			,
				"http_code"	=> 0			,
				"curl_error"	=> curl_errno($curl)	,
				"message"	=> curl_error($curl)
			];
		}else{
			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		    	@curl_close($curl);

			// int is more readable in print_r

			return [
				"ok"		=> $code == 200 ? 1 : 0		,
				"http_code"	=> $code			,
				"curl_error"	=> curl_errno($curl)		,
				"message"	=> $code == 200 ? "" : $result
			];
		}
	}
};

