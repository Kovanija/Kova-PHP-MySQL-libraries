<?php

	
    class FileIO
    {
        //file info
        private $fileSizeMax;
        private $fileSizeMin = 1;
        //file configuration
        private $extensions = array("jpg", "jpeg", "png");

        function __construct()
        {
			$this->fileSizeMax = trim(ini_get('upload_max_filesize'), "M");
			$this->fileSizeMax *= (1024*1024);
        }

        function set_file_configuration($allowedExtensions, $minFileSize)
        {
            if(is_long($minFileSize) || $minFileSize == null && is_array($allowedExtensions) || $allowedExtensions == null)
                {
                  ($minFileSize == null) ?: $this->fileSizeMin = $minFileSize;
                  ($allowedExtensions == null) ?: $this->extensions = $allowedExtensions;
                }
                else
				    print_r("Wrong arguments, expected example : set_file_configuration(array('png, jpg, zip, js'), 10)");

        }

        function get_file_configuration()
        {
		   $extensions = implode(",", $this->extensions);
           return "\nMinimum file size : ".$this->fileSizeMin."B\nMaximum file size : ".$this->fileSizeMax." | Maximum file size can be reconfigured in php.ini\nExtensions allowed : ".$extensions;
        }

        function file_upload($fileName, $filePath)
        {
			if(self::check_extension($fileName) == true)
			{
				if(self::check_size($fileName) == true)
				{
					if(move_uploaded_file($fileName['tmp_name'], $filePath.$fileName['name']))
						print_r("File uploaded into images");
					else
						print_r("File is not uploaded, check file size, permissions for writing in that folder and if folder exists!");
				}	
				else
					print_r("File size must be lower than ".$this->fileSizeMax.".");
			} 
			else
				print_r("Extension not allowed! check get_file_configuration() for allowed extensions!");
        }
		
		private function check_extension($file)
		{
			$getName = $file['name'];
			$ext = explode('.', (string)$getName);
			$getName = end($ext);

			if(in_array($getName, $this->extensions) == false)
				return false;
            else
				return true;
		}
		
		private function check_size($file)
		{
			$getSize = $file['size'];
	
			if($file['size'] < $this->fileSizeMax)
				return true;
			else
				return false;
		}
    }
    
?>