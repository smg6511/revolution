import React, { useContext, useState } from 'react';

/* eslint-disable arrow-body-style */
const TvFormGeneral = () => {
    const [tvName, setTvName] = useState('');
    const handleChangeName = e => {
        setTvName(e.target.value);
    };
    const [tvCaption, setTvCaption] = useState('');
    const handleChangeCaption = e => {
        setTvCaption(e.target.value);
    };
    const tvCtx = useContext(TvCont)
    return (
        <React.Fragment>
            <div className="form-control">
                <label htmlFor="tv-name">TV Name</label>
                <input
                    type="text"
                    id="tv-name"
                    name="tv-name"
                    value={tvName}
                    onChange={handleChangeName}
                />
                <div className="text-sm">Field desc</div>
            </div>
            <div className="form-control">
                <label htmlFor="tv-name">TV Caption</label>
                <input
                    type="text"
                    id="tv-caption"
                    name="tv-caption"
                    value={tvCaption}
                    onChange={handleChangeCaption}
                />
                <div className="text-sm">Field desc</div>
            </div>
        </React.Fragment>
    );
};
export default TvFormGeneral;
