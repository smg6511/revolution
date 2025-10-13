/**
 *
 * @param classes
 * @param param1
 */
import React from 'react';
import { BaseProps } from '@/app/models/base';
import style from './Panel.module.scss';

// eslint-disable-next-line arrow-body-style
// const Panel: React.FC<BaseProps> = ({ classes, children }) => {
const Panel = ({ classes, children }: BaseProps): React.JSX.Element => {
    const aTest: number = 1;
    return (
    <div className={`${style.panel} ${classes}`}>
        <header>
            <h1>Panel Header {aTest}</h1>
            <div>Panel Intro</div>
        </header>
        {children}
    </div>
    );
};
export default Panel;
