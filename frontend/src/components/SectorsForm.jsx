import { useState, useEffect } from 'react';
import axios from "axios";

function SectorsForm() {
  const [fetchedSectors, setFetchedSectors] = useState([]);
  const [formData, setFormData] = useState({
    username: "",
    terms: false,
    sectors: []
  });

  const sectorsUrl = 'http://localhost/sector_management_project_backend/api/get_sectors_from_db.php'

   useEffect(() => {
    const fetchSectors = async () => {
      try {
        const res = await fetch(sectorsUrl);
        const data = await res.json();
        const tree = buildSectorTree(data);
        const flat = flattenSectorTree(tree);
        setFetchedSectors(flat);
      } catch (err) {
        console.error("Error fetching sectors:", err);
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

  const handleSubmit = (event) => {
    event.preventDefault();

    axios
      .post("", formData) //REPLACE WITH SAVE USER FILE
      .then((response) => {
        console.log(response);
      })
      .catch((error) => {
        console.log(error);
      });
  }

  return (
    <div className="">
      <form onSubmit={handleSubmit}>
        <h1>Please enter your name and pick the Sectors you are currently involved in.</h1>
        <label>Name:
          <input 
            type="text" 
            id="name_input"
            name="username" 
            value={formData.username}
            onChange={handleInputChange}
          />
        </label>

        <label>
          Sectors:
          <select
            name="sectors"
            multiple
            value={formData.sectors}
            onChange={handleInputChange}
            size="5"
          >
            {fetchedSectors.map((sector) => (
              <option key={sector.sector_id} value={sector.sector_id}>
                {sector.sector_name}
              </option>
            ))}
          </select>
        </label>

        <label>Agree to terms
          <input 
            type="checkbox" 
            id="terms_checkbox" 
            name="terms"
            checked={formData.terms}
            onChange={handleInputChange}
          />
        </label>
        <br/>
        <input type="submit" value="save" />
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

function flattenSectorTree(tree, depth = 0) { //flatten for easier management
  let result = [];
  for (const node of tree) {
    result.push({
      sector_id: node.sector_id,
      sector_name: `${"\u00A0\u00A0\u00A0\u00A0".repeat(depth)}${node.sector_name}`,
    });
    result = result.concat(flattenSectorTree(node.children, depth + 1));
  }
  return result;
}