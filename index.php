<?php
/**
 * Data Base Hub
 * Crontab application which collect databases backup files by FTP
 */

/**
 * Setup FTP host parameters
 */
$ftp_host = "";
$ftp_port = "";
$ftp_timeout = "300";
$ftp_username = '';
$ftp_password = "";
$serverName = "Application: DataBaseHub\nSender: ";

/**
 * Setup telegram bot for notification us of results.
 */

/* Telegram bot configuration */
const URL = 'https://api.telegram.org/bot';
const TOKEN = '';
const CHANNEL_NAME = "chat_id=@"; // Test telegram channel

/* Methods */
$method_getUpdates = '/getupdates';
$method_sendMessage = '/sendmessage?';

/**
 * Setup path's to backup file:
 */
$back_up_path = ''; // path to backup file
$remote_file_path = ""; // remote path on ftp server

/**
 * Connecting to FTP server;
 */
$ftp_connection = ftp_connect($ftp_host,$ftp_port,$ftp_timeout);
if(!$ftp_connection){
    // Sending notification to telegram channel
    $text = urlencode($serverName."\nCan't connect to FTP server: ");
    file_get_contents(URL.TOKEN.$method_sendMessage.CHANNEL_NAME.'&text='.$text.$ftp_host);
    exit();
}else{
    $ftp_login = ftp_login($ftp_connection,$ftp_username,$ftp_password);
    ftp_pasv($ftp_connection,true); // Enabling passive mode

    if(!$ftp_login){
        $text = urlencode($serverName."\nCan't login to FTP server: ".$ftp_host."\nPlease check Username or Password!");
        file_get_contents(URL.TOKEN.$method_sendMessage.CHANNEL_NAME.'&text='.$text);
        exit();
    }else{
        if (ftp_chdir($ftp_connection, $remote_file_path)) {
            $file_names = scandir($back_up_path,1);
            $file_name = $file_names[0];
            // Put backup database to FTP server:
            $ftp_sending = ftp_put( $ftp_connection,$remote_file_path.$file_name,$back_up_path.$file_name,FTP_ASCII);
            if($ftp_sending){
                // Sending notification to telegram channel
                $text = urlencode($serverName."\nFile: ".$file_name."\nSuccessfully uploaded to: ".$ftp_host);
                file_get_contents(URL.TOKEN.$method_sendMessage.CHANNEL_NAME.'&text='.$text);
            }else{
                // Sending notification to telegram channel
                $text = urlencode($serverName."\nError: Can't upload backup file: ".$file_name);
                file_get_contents(URL.TOKEN.$method_sendMessage.CHANNEL_NAME.'&text='.$text);
            }
        } else {
            $text = urlencode($serverName."\nError: Can't find directory ".$remote_file_path."\nOn ftp server: ".$ftp_host);
            file_get_contents(URL.TOKEN.$method_sendMessage.CHANNEL_NAME.'&text='.$text);
        }
    }
    ftp_close($ftp_connection);
}