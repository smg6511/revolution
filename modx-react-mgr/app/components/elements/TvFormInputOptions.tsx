import React from 'react';

/* eslint-disable arrow-body-style */
const TvFormInputOptions = () => {
    return (
        <React.Fragment>
            <div className="form-control">
                <label htmlFor="tv-input-type">Input Type</label>
                <input
                    type="text"
                    id="tv-input-type"
                    name="tv-input-type"
                    defaultValue="text"
                />
                <div className="text-sm">Field desc</div>
            </div>
        </React.Fragment>
    );
};
export default TvFormInputOptions;
