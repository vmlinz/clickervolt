<?php

namespace ClickerVolt;

class FileTools
{
    /**
     * 
     */
    static function atomicSave( $filename, $data )
    {
        file_put_contents( $filename, $data, LOCK_EX );
    }
    
    /**
     * 
     */
    static function atomicLoad( $filename )
    {
        $fileHandler = fopen( $filename, 'rt' );
        flock( $fileHandler, LOCK_EX );
        $fileSize = filesize( $filename );
        $content = ( $fileSize ? fread( $fileHandler, filesize( $filename ) ) : null );
        fclose( $fileHandler );
        return $content;
    }
    
    /**
     * 
     */
    static function delete( $filename )
    {
        if ( file_exists( $filename ) ) {
            unlink( $filename );
        }
    }
    
    /**
     * 
     */
    static function log( $message, $path )
    {
        file_put_contents( $path, date( 'Y-m-d H:i:s' ) . ": {$message}" . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX );
    }
    
    /**
     * 
     */
    static function logInfo( $message )
    {
        self::log( $message, self::getAdminTmpFolderPath() . '/log-info.txt' );
    }
    
    /**
     * @param $path string
     * @param $search string
     * @return array
     */
    static function searchInFile( $path, $search )
    {
        $entries = [];
        $handle = fopen( $path, "r" );
        
        if ( $handle ) {
            while ( !feof( $handle ) ) {
                $line = fgets( $handle );
                if ( strpos( $line, $search ) !== false ) {
                    $entries[] = rtrim( $line, "\r\n" );
                }
            }
            fclose( $handle );
        }
        
        return $entries;
    }
    
    /**
     * 
     */
    static function getPluginFolderPath( $subPath = null )
    {
        $foldersTree = [ '..' ];
        if ( $subPath ) {
            $foldersTree[] = $subPath;
        }
        $path = __DIR__ . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $foldersTree );
        if ( !file_exists( $path ) ) {
            mkdir( $path, 0755 );
        }
        return realpath( $path );
    }
    
    /**
     * @return string
     */
    static function getPluginFolderName()
    {
        return 'clickervolt';
    }
    
    /**
     * 
     */
    static function getAdminTmpFolderPath()
    {
        return self::getPluginFolderPath( 'admin/tmp' );
    }
    
    /**
     * 
     */
    static function getDataFolderPath( $subPath = null )
    {
        $foldersTree = [ '..', '..', 'clickervolt-data' ];
        if ( $subPath ) {
            $foldersTree[] = $subPath;
        }
        $path = __DIR__ . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $foldersTree );
        if ( !file_exists( $path ) ) {
            mkdir( $path, 0755, true );
        }
        return realpath( $path );
    }
    
    /**
     * 
     * @param $forceUpdate bool - if true, then we re-cache the blog's absolute path
     * @return string AbsPath
     * @throws \Exception
     */
    static function getAbsPath( $forceUpdate = false )
    {
        $path = self::getDataFolderPath( 'misc' ) . '/abs-path';
        if ( $forceUpdate || !file_exists( $path ) ) {
            if ( false === file_put_contents( $path, ABSPATH ) ) {
                throw new \Exception( "Cannot write absolute path to disk" );
            }
        }
        $content = file_get_contents( $path );
        if ( empty($content) || !is_string( $content ) ) {
            throw new \Exception( "Cannot load absolute path from disk" );
        }
        return $content;
    }
    
    /**
     * @param string $archivePath
     * @param array $files
     * @param string $filesRoot
     * @throws \Exception
     */
    static function zip( $archivePath, $files, $filesRoot )
    {
        require_once self::getAbsPath() . 'wp-admin/includes/class-pclzip.php';
        self::delete( $archivePath );
        $archive = new \PclZip( $archivePath );
        if ( !$archive->create( $files, PCLZIP_OPT_REMOVE_PATH, $filesRoot ) ) {
            throw new \Exception( "Can't create archive '{$archivePath}': {$archive->error_string}" );
        }
    }

}