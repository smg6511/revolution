import React from 'react';

/* eslint-disable arrow-body-style */
const TvFormOutputOptions = () => {
    return (
        <React.Fragment>
            <div className="form-control">
                <label htmlFor="tv-output-type">Output Type</label>
                <input
                    type="text"
                    id="tv-output-type"
                    name="tv-output-type"
                    defaultValue="text"
                />
                <div className="text-sm">Field desc</div>
            </div>
        </React.Fragment>
    );
};
export default TvFormOutputOptions;
