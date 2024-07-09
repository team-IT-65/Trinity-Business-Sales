<?php

namespace Wpsec\twofa\Services\container;

# This implementation of container is modified Pipmle container implementation https://github.com/silexphp/Pimple/blob/main/src/Pimple/Container.php

class Container implements \ArrayAccess {

	private $values = array();
	private $raw    = array();
	private $keys   = array();


	public function __construct( array $values = array() ) {
		foreach ( $values as $key => $value ) {
			$this->offsetSet( $key, $value );
		}
	}

	public function offsetSet( $id, $value ): void {
		$this->values[ $id ] = $value;
		$this->keys[ $id ]   = true;
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $id ) {

		if ( ! isset( $this->keys[ $id ] ) ) {
			throw new \Exception( \sprintf( 'Service "%s" is not defined.', $id ) );
		}

		if (
			isset( $this->raw[ $id ] )
			|| ! \is_object( $this->values[ $id ] )
			|| ! \method_exists( $this->values[ $id ], '__invoke' )
		) {
			return $this->values[ $id ];
		}

		$raw                 = $this->values[ $id ];
		$val                 = $raw( $this );
		$this->values[ $id ] = $val;
		$this->raw[ $id ]    = $raw;

		return $val;
	}

	public function offsetExists( $id ): bool {
		return isset( $this->keys[ $id ] );
	}

	public function offsetUnset( $id ): void {
		if ( isset( $this->keys[ $id ] ) ) {
			unset( $this->values[ $id ], $this->raw[ $id ], $this->keys[ $id ] );
		}
	}
}
