import { usePage } from '@inertiajs/react';

export default function App() {
    const { component, props } = usePage();
    const Component = component;

    return <Component {...props} />;
}
