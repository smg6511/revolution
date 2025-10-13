/* eslint-disable import/extensions */
/* eslint-disable semi-style */
/* eslint-disable one-var */
import React, { useReducer } from 'react';
import { ElementActions, TvElementState } from '../models/elements';

export const TvContext = React.createContext({
    data: {
        general: {},
        input: {},
        output: {}
    },
    errors: 0,
    composeData: () => {},
    saveTv: () => {},
    deleteTv: id => {}
});

const initialState = {
    data: {},
    errors: 0
};

const tvReducer = (state: TvElementState, action: ElementActions) => {
    switch (action.type) {
        case 'COMPOSE':
            return state;
        default:
            return initialState;
    }
};

const TvProvider = () => {
    const [tvState, dispatchTvAction] = useReducer(tvReducer, initialState);

    const
        handleComposeTv = () => {},
        handleAddTv = () => {},
        handleDeleteTv = (id) => {}
    ;

    const tvCtx: TvElementState = {
        data: {
            general: tvState.data.general,
            input: tvState.data.input,
            output: tvState.data.output
        },
        composeData: handleComposeTv,
        saveTv: handleAddTv,
        deleteTv: handleDeleteTv,
        errors: tvState.errors
    };

    return (
        <TvContext.Provider value={tvCtx}>

        </TvContext.Provider>
    );
};

export default TvProvider;
