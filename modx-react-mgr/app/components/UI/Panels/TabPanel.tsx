import React from 'react';
// import { BaseProps } from '@/app/models/base';
import Link from 'next/link';
// import { useRouter } from 'next/router';
import { ElementProps } from '@/app/models/elements';

const TabPanel = ({
    children, classes, selectedTab, config
}: ElementProps): React.JSX.Element => {
    const
        ContentComponent = config[selectedTab].cmp
    ;
    // const whichPanel = orientation === 'horz' ? 'HORZ' : 'VERT' ;

    // const router = useRouter();
    return (
        <div className={`tab-panel ${classes}`}>
            {/* Tab Links */}
            <ul className='flex gap-2'>
                {/* Tab */}
                <li><Link href='/tv/general'>Tab One</Link></li>
                <li><Link href='/tv/input'>Tab Two</Link></li>
                <li><Link href='/tv/output'>Tab Three</Link></li>
            </ul>
            <div>
                {/* <div>Content One</div>
                <div>Content Two</div> */}
                <ContentComponent />
            </div>
            {children}
        </div>
    );
}
export default TabPanel;
