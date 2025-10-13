/**
 * Info...
 */
// import React from 'react';
import { BaseProps } from './base';

export interface ElementProps extends BaseProps {
    selectedTab: string,
    config: {}
}
export interface ElementState {
    errors: number
}
export interface TvElementState extends ElementState {
    data: {
        general: {},
        input: {},
        output: {}
    }

}

type ElementCompose = { type: 'COMPOSE' };
type ElementAdd = { type: 'ADD', payload: {} };
type ElementDelete = { type: 'ADD', payload: number | string };
export type ElementActions = ElementCompose | ElementAdd | ElementDelete;
