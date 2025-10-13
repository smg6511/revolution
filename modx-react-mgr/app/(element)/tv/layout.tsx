import React from 'react';

const TvLayout = ({ children }: Readonly<{children: React.ReactNode}>) => {
    const test = "test";
    return (
        <section className='tv'>
            <header>
                <h2 className='text-[1.3rem]'>Create|Edit TV: ...</h2>
            </header>
            {/* <p>This is the TV layout...</p> */}
            {children}
        </section>
    );
};

export default TvLayout;
