import React from 'react';

const ElementLayout = ({ children }: Readonly<{children: React.ReactNode}>) => {
    const test = '';
    return (
        <section className='element'>
            <p>This is the Element layout...</p>
            {children}
        </section>
    );
};

export default ElementLayout;
