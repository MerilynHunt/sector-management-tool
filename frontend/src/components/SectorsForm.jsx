import { useState, useEffect } from 'react';
import axios from 'axios';

axios.defaults.withCredentials = true;

function SectorsForm() {
  const [fetchedSectors, setFetchedSectors] = useState([]);
  const [isEditMode, setIsEditMode] = useState(false);
  const [formData, setFormData] = useState({
    username: "",
    terms: false,
    sectors: []
  });
  const sectorsUrl = "http://localhost/sector_management_project_backend/api/get_sectors_from_db.php";
  const userUrl = "http://localhost/sector_management_project_backend/api/get_user_from_db.php";

   useEffect(() => {
    const fetchSectors = async () => {
      try {
        //sectors
        const res = await fetch(sectorsUrl);
        const data = await res.json();
        const tree = buildSectorTree(data);
        const flat = flattenSectorTree(tree);
        setFetchedSectors(flat);

        //saved user data
        const userRes = await axios.get(userUrl, { withCredentials: true });
        const userData = userRes.data;

        if (userData.user === null) {
          setFormData({
            username: '',
            terms: false,
            sectors: [],
          });
          setIsEditMode(false);
        } else {
          setFormData({
            username: userData.username || '',
            terms: userData.terms || false,
            sectors: userData.sectors || [],
          });
          setIsEditMode(true);
        }

      } catch (err) {
        console.error("Error fetching sectors and user:", err);
      }
    };
    fetchSectors();
  }, []);

  const handleInputChange = (event) => {
    const { name, value, type, checked } = event.target;

    if (type === "checkbox") {
      setFormData((prevData) => ({
        ...prevData,
        [name]: checked,
      }));
    } else if (type === "select-multiple") {
      const selectedOptions = Array.from(event.target.selectedOptions).map(option => option.value);
      setFormData((prevData) => ({
        ...prevData,
        [name]: selectedOptions,
      }));
    } else {
      setFormData((prevData) => ({
        ...prevData,
        [name]: value,
      }));
    }
  };

  const saveDataUrl = isEditMode
    ? "http://localhost/sector_management_project_backend/api/update_user_in_db.php"
    : "http://localhost/sector_management_project_backend/api/save_user_to_db.php";

  const handleSubmit = (event) => {
    event.preventDefault();

    axios
      .post(saveDataUrl, formData, { withCredentials: true })
      .then((response) => {
      console.log(response);

      const data = response.data;
      if (data.success && data.user_id) {
        setIsEditMode(true);
      }

      if (data.deleted) {
        setFormData({
          username: '',
          terms: false,
          sectors: [],
        });
        
        setIsEditMode(false);

        alert("Your data has been deleted.");
      } else if (data.success || data.updated) {
        alert("Your data has been saved.");
      }
    })
      .catch((error) => {
        console.log(error);
      });
  }

  return (
    <div className="mt-12 flex flex-col items-center">
        <form onSubmit={handleSubmit}>
            <h1 className='pb-6 text-xl justify-self-center'>Please enter your name and pick the Sectors you are currently involved in.</h1>

            {/* name */}
            <div className='pb-6 text-lg flex flex-col items-start w-full'>
                <label htmlFor="name_input" className="mb-2">Name:</label>
                <input 
                    type="text" 
                    id="name_input"
                    name="username" 
                    value={formData.username}
                    onChange={handleInputChange}
                    className="border border-gray-400 rounded px-2 py-1 w-full max-w-md"
                />
            </div>

            <div className="pb-6 text-lg flex flex-col items-start w-full">
                <label htmlFor="sectors_select" className="mb-2">Sectors:</label>

                {/* selected tags */}
                <div className="flex flex-wrap gap-2 mb-4 w-full max-w-md">
                    {formData.sectors.map((sectorId) => {
                    const sector = fetchedSectors.find((s) => s.sector_id == sectorId);
                    return (
                        <div
                        key={sectorId}
                        className="flex items-center bg-gray-200 rounded-full px-3 py-1 text-sm"
                        >
                        {sector?.original_name}
                        <button
                            type="button"
                            className="ml-2 text-gray-600 hover:text-red-600 font-bold"
                            onClick={() =>
                            setFormData((prev) => ({
                                ...prev,
                                sectors: prev.sectors.filter((id) => id !== sectorId),
                            }))
                            }
                        >
                            ×
                        </button>
                        </div>
                    );
                    })}
                </div>

                {/* dropdown */}
                <select
                    onChange={(e) => {
                    const value = e.target.value;
                    if (!formData.sectors.includes(value)) {
                        setFormData((prev) => ({
                        ...prev,
                        sectors: [...prev.sectors, value],
                        }));
                    }
                    e.target.value = "";
                    }}
                    id="sectors_select"
                    className="border border-gray-400 rounded px-2 py-1 w-full max-w-md"
                    value=""
                >
                    <option value="" disabled>Select a sector</option>
                    {fetchedSectors
                    .filter((s) => !formData.sectors.includes(s.sector_id))
                    .map((sector) => (
                        <option key={sector.sector_id} value={sector.sector_id}>
                        {sector.sector_name}
                        </option>
                    ))}
                </select>
            </div>

            {/* terms */}
            <div className="pb-2 text-lg flex flex-col">
                <label className="pb-6">Agree to terms
                <input 
                    type="checkbox" 
                    id="terms_checkbox" 
                    name="terms"
                    checked={formData.terms}
                    onChange={handleInputChange}
                    className="ml-4 border border-gray-400 rounded"
                />
                </label>

            <input type="submit" value={isEditMode ? "Update" : "Save"} className="px-4 py-1 self-start border border-gray-400 hover:bg-gray-200 rounded" />
            </div>
        </form>
    </div>
  )
}

export default SectorsForm

function buildSectorTree(sectors, parentId = null) { //build tree to ensure flexibility
  return sectors
    .filter(sector => sector.sector_parent_id === parentId)
    .map(sector => ({
      ...sector,
      children: buildSectorTree(sectors, sector.sector_id),
    }));
}

function flattenSectorTree(tree, depth = 0) { //flatten tree for easier management
  let result = [];
  for (const node of tree) {
    const decodedName = decodeHTMLEntities(node.sector_name);
    result.push({
      sector_id: node.sector_id,
      sector_name: `${"\u00A0\u00A0\u00A0\u00A0".repeat(depth)}${decodedName}`,
      original_name: decodedName,
    });
    result = result.concat(flattenSectorTree(node.children, depth + 1));
  }
  return result;
}

function decodeHTMLEntities(text) { //decode escaped html & signs
  const textarea = document.createElement('textarea');
  textarea.innerHTML = text;
  return textarea.value;
}