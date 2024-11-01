<?php

class VkCommerce_Logger {
	const LEVEL_ERROR = 'error';

	/**
	 * @var VkCommerce_Logger
	 */
	protected static $_instance = null;

	/**
	 * @var int
	 */
	private $log_size_limit;

	/**
	 * @var resource[]
	 */
	private $handles = array();

	/**
	 * @return VkCommerce_Logger
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		$this->log_size_limit = (int) ( 5 * 1024 * 1024 );
	}

	protected function open( $handle, $mode = 'a' ) {
		if ( $this->is_open( $handle ) ) {
			return true;
		}

		$file = $this->get_log_file_path( $handle );

		if ( $file ) {
			if ( ! file_exists( $file ) ) {
				$temp_handle = @fopen( $file, 'w+' ); // @codingStandardsIgnoreLine.
				if ( is_resource( $temp_handle ) ) {
					@fclose( $temp_handle ); // @codingStandardsIgnoreLine.

					if ( defined( 'FS_CHMOD_FILE' ) ) {
						@chmod( $file, FS_CHMOD_FILE ); // @codingStandardsIgnoreLine.
					}
				}
			}

			$resource = @fopen( $file, $mode ); // @codingStandardsIgnoreLine.

			if ( $resource ) {
				$this->handles[ $handle ] = $resource;

				return true;
			}
		}

		return false;
	}

	protected function is_open( $handle ) {
		return array_key_exists( $handle, $this->handles ) && is_resource( $this->handles[ $handle ] );
	}

	protected function close( $handle ) {
		$result = false;

		if ( $this->is_open( $handle ) ) {
			$result = fclose( $this->handles[ $handle ] ); // @codingStandardsIgnoreLine.
			unset( $this->handles[ $handle ] );
		}

		return $result;
	}

	private function should_rotate( $handle ) {
		$file = $this->get_log_file_path( $handle );

		if ( $file ) {
			if ( $this->is_open( $handle ) ) {
				$file_stat = fstat( $this->handles[ $handle ] );

				return $file_stat['size'] > $this->log_size_limit;
			} elseif ( file_exists( $file ) ) {
				return filesize( $file ) > $this->log_size_limit;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	private function log_rotate( $handle ) {
		for ( $i = 8; $i >= 0; $i -- ) {
			$this->increment_log_infix( $handle, $i );
		}

		$this->increment_log_infix( $handle );
	}

	private function increment_log_infix( $handle, $number = null ) {
		if ( null === $number ) {
			$suffix      = '';
			$next_suffix = '.0';
		} else {
			$suffix      = '.' . $number;
			$next_suffix = '.' . ( $number + 1 );
		}

		$rename_from = $this->get_log_file_path( "{$handle}{$suffix}" );
		$rename_to   = $this->get_log_file_path( "{$handle}{$next_suffix}" );

		if ( $this->is_open( $rename_from ) ) {
			$this->close( $rename_from );
		}

		if ( is_writable( $rename_from ) ) {
			return rename( $rename_from, $rename_to );
		} else {
			return false;
		}

	}

	private function get_log_file_path( $handle ) {
		if ( ! function_exists( 'wp_hash' ) ) {
			return false;
		}

		return trailingslashit( VKCOMMERCE_LOG_DIR ) . $this->get_log_file_name( $handle );
	}

	private function get_log_file_name( $handle ) {
		if ( ! function_exists( 'wp_hash' ) ) {
			return false;
		}

		$date_suffix = date( 'Y-m-d', time() );
		$hash_suffix = wp_hash( $handle );

		return sanitize_file_name( implode( '-', array( $handle, $date_suffix, $hash_suffix ) ) . '.log' );
	}

	private function format_entry( $level, $message ) {
		$time_string  = date( 'c' );
		$level_string = strtoupper( $level );

		return "{$time_string} {$level_string} {$message}";
	}

	/**
	 * @param string $level
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	private function log( $level, $message, $context = array() ) {
		if ( isset( $context['source'] ) && $context['source'] ) {
			$handle = $context['source'];
		} else {
			$handle = 'log';
		}

		if ( $this->should_rotate( $handle ) ) {
			$this->log_rotate( $handle );
		}

		$entry = $this->format_entry( $level, $message );

		if ( $this->open( $handle ) && is_resource( $this->handles[ $handle ] ) ) {
			fwrite( $this->handles[ $handle ], $entry . PHP_EOL );

			$this->close( $handle );
		}
	}

	/**
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function error( $message, $context = array() ) {
		$this->log( self::LEVEL_ERROR, $message, $context );
	}
}
