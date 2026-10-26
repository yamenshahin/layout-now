/**
 * Lightweight toast notifications.
 */
import { createContext, useCallback, useContext, useRef, useState } from '@wordpress/element';

const ToastContext = createContext( () => {} );

export function ToastProvider( { children } ) {
	const [ toasts, setToasts ] = useState( [] );
	const next = useRef( 0 );

	const push = useCallback( ( message, type = 'success' ) => {
		const id = ++next.current;
		setToasts( ( list ) => [ ...list, { id, message, type } ] );
		window.setTimeout( () => {
			setToasts( ( list ) => list.filter( ( toast ) => toast.id !== id ) );
		}, type === 'error' ? 6000 : 2600 );
	}, [] );

	return (
		<ToastContext.Provider value={ push }>
			{ children }
			<div className="lnow-toasts" role="status" aria-live="polite">
				{ toasts.map( ( toast ) => (
					<div key={ toast.id } className={ `lnow-toast is-${ toast.type }` }>
						{ toast.message }
					</div>
				) ) }
			</div>
		</ToastContext.Provider>
	);
}

export const useToast = () => useContext( ToastContext );
