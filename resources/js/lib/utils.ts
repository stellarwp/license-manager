import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn( ...inputs: ClassValue[] ): string {
    return twMerge( clsx( inputs ) );
}

export function randomUUID(): string {
    if ( typeof crypto.randomUUID === 'function' ) {
        return crypto.randomUUID();
    }
    return ( [ 1e7 ] as unknown as number[] )
        .concat( -1e3, -4e3, -8e3, -1e11 )
        .join( '' )
        .replace( /[018]/g, ( c ) => {
            const n = Number( c );
            return ( n ^ ( crypto.getRandomValues( new Uint8Array( 1 ) )[ 0 ] & ( 15 >> ( n / 4 ) ) ) ).toString( 16 );
        } );
}
