/**
 * Inline icons (24px grid, stroke-based) to avoid bundling an icon library.
 */
const Icon = ( { children, size = 20, ...props } ) => (
	<svg
		width={ size }
		height={ size }
		viewBox="0 0 24 24"
		fill="none"
		stroke="currentColor"
		strokeWidth="1.75"
		strokeLinecap="round"
		strokeLinejoin="round"
		aria-hidden="true"
		focusable="false"
		{ ...props }
	>
		{ children }
	</svg>
);

export const ArrowLeft = ( p ) => (
	<Icon { ...p }>
		<path d="M19 12H5M11 18l-6-6 6-6" />
	</Icon>
);
export const Plus = ( p ) => (
	<Icon { ...p }>
		<path d="M12 5v14M5 12h14" />
	</Icon>
);
export const Desktop = ( p ) => (
	<Icon { ...p }>
		<rect x="3" y="4" width="18" height="12" rx="2" />
		<path d="M8 20h8M12 16v4" />
	</Icon>
);
export const Tablet = ( p ) => (
	<Icon { ...p }>
		<rect x="5" y="3" width="14" height="18" rx="2" />
		<path d="M11 18h2" />
	</Icon>
);
export const Mobile = ( p ) => (
	<Icon { ...p }>
		<rect x="7" y="3" width="10" height="18" rx="2" />
		<path d="M11 18h2" />
	</Icon>
);
export const Copy = ( p ) => (
	<Icon { ...p }>
		<rect x="9" y="9" width="11" height="11" rx="2" />
		<path d="M5 15V6a2 2 0 0 1 2-2h9" />
	</Icon>
);
export const Check = ( p ) => (
	<Icon { ...p }>
		<path d="M5 12.5l4.5 4.5L19 7.5" />
	</Icon>
);
export const Dots = ( p ) => (
	<Icon { ...p }>
		<circle cx="5" cy="12" r="1" fill="currentColor" />
		<circle cx="12" cy="12" r="1" fill="currentColor" />
		<circle cx="19" cy="12" r="1" fill="currentColor" />
	</Icon>
);
export const Sun = ( p ) => (
	<Icon { ...p }>
		<circle cx="12" cy="12" r="4" />
		<path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
	</Icon>
);
export const Moon = ( p ) => (
	<Icon { ...p }>
		<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z" />
	</Icon>
);
export const Sparkle = ( p ) => (
	<Icon { ...p }>
		<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z" />
	</Icon>
);
export const ChevronDown = ( p ) => (
	<Icon { ...p }>
		<path d="M6 9l6 6 6-6" />
	</Icon>
);
