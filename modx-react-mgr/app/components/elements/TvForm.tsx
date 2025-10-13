/* eslint-disable arrow-body-style */
const TvForm = () => {
    return (
        <form>
            {/* TV Form Here */}
            <div className="form-control">
                <label htmlFor="tvName">Name</label>
                <input
                    type="text"
                    id="tvName"
                    name="tvName"
                    defaultValue="test"
                />
                <div className="text-sm">Field desc</div>
            </div>
        </form>
    );
};
export default TvForm;
