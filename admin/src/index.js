/**
 * Layout Now admin app.
 */
import { createRoot, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';
import { api } from './api';
import { useRoute } from './router';
import { ToastProvider } from './toasts';
import LayoutsList from './screens/LayoutsList';
import NewLayout from './screens/NewLayout';
import Editor from './screens/Editor';
import './app.scss';

function App() {
	const route = useRoute();
	const [ schema, setSchema ] = useState( null );
	const [ error, setError ] = useState( null );

	useEffect( () => {
		api.schema()
			.then( setSchema )
			.catch( ( err ) => setError( err?.message || __( 'Layout Now could not load.', 'layout-now' ) ) );
	}, [] );

	if ( error ) {
		return <div className="lnow-fatal">{ error }</div>;
	}
	if ( ! schema ) {
		return (
			<div className="lnow-loading">
				<Spinner />
			</div>
		);
	}

	if ( route.view === 'edit' ) {
		// Stable key: saving a new layout swaps the URL to its ID without remounting the editor.
		return <Editor key="editor" schema={ schema } route={ route } />;
	}
	if ( route.view === 'new' ) {
		return <NewLayout schema={ schema } />;
	}
	return <LayoutsList schema={ schema } />;
}

const mount = document.getElementById( 'layout-now-app' );
if ( mount ) {
	createRoot( mount ).render(
		<ToastProvider>
			<div className="lnow-app">
				<App />
			</div>
		</ToastProvider>
	);
}
