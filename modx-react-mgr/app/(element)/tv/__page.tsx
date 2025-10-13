import React from 'react';

import Panel from '@/app/components/UI/Panels/Panel';
import TvForm from '@/app/components/elements/TvForm';
import TabPanel from '@/app/components/UI/Panels/TabPanel';

const TvPanel = () => {
    const test: number = 0;
    return (
        <Panel classes='myCss'>
            <TabPanel classes='bg-cyan-200 p-4'>
                {/* <TvForm testProp={test} /> */}
            </TabPanel>
        </Panel>
    );
};

export default TvPanel;
