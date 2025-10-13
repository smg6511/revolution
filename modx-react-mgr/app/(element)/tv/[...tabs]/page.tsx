'use client'

import React from 'react';
import { usePathname, useSearchParams } from 'next/navigation'

import Panel from '@/app/components/UI/Panels/Panel';
// import TvForm from '@/app/components/elements/TvForm';
import TabPanel from '@/app/components/UI/Panels/TabPanel';

import TvFormGeneral from '@/app/components/elements/TvFormGeneral';
import TvFormInputOptions from '@/app/components/elements/TvFormInputOptions';
import TvFormOutputOptions from '@/app/components/elements/TvFormOutputOptions';

const TvPanel = () => {
    // const test: number = 0;
    // eslint-disable-next-line one-var
    const
        path = usePathname(),
        tabKey = path.split('/').pop() || 'general',
        tabsConfig = {
            general: {
                label: 'General Information',
                description: 'Here you can enter the basic attributes for this Template Variable (TV). Note that TVs must be assigned to templates in order to access them from snippets and documents.',
                cmp: TvFormGeneral
            },
            input: {
                label: 'Input Options',
                description: 'Here you can edit the input options for the TV, specific to the type of input render that you select.',
                cmp: TvFormInputOptions
            },
            output: {
                label: 'Output Options',
                description: 'Here you can edit the output options for the TV, specific to the type of output render that you select.',
                cmp: TvFormOutputOptions
            }
        }
    ;

    return (
        <Panel classes='myCss'>
            <form>
                <TabPanel classes='bg-cyan-200 p-4' selectedTab={tabKey} config={tabsConfig} />
                <p>The path is {path}</p>
                <p>Data key: {tabKey}</p>
            </form>
        </Panel>
    );
};

export default TvPanel;
