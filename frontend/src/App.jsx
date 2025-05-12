import './App.css'

function App() {

  return (
    <div className="flex flex-col">
      <div>
        <h1>Please enter your name and pick the Sectors you are currently involved in.</h1>
        <label for="name_input">Name:</label>
        <input type="text" name="name_input" id="name_input" />
      </div>

      <div>
        <label for="terms_checkbox">Agree to terms</label>
        <input type="checkbox" name="terms_checkbox" id="terms_checkbox" />
        <br/>
        <input type="submit" value="Save" />
      </div>
    </div>
  )
}

export default App
