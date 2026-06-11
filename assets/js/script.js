var data_json = {};
let results = jQuery('.results');
let loading = jQuery('.results .loading');
let nextBtn = document.getElementById("nextBtn");
let prevBtn = document.getElementById("prevBtn");
let tryAgainButton = document.getElementById("tryBtn");
let prevBtnResults = document.getElementById("prevBtnResults");
let result = jQuery("#result");
let quote = document.querySelector('[data-id="offer_presentation_price"]');
let error =  jQuery("#error");
let searchVinPhone = document.getElementById("searchVinPhone");
tryAgainButton.style.display = "none";
prevBtnResults.style.display = "none";
searchVinPhone.style.display = "none";
var currentTab = 0;
var vin_selected = false;
document.addEventListener("DOMContentLoaded", function () {
  if(isObjectEmpty(selected))
  {
    getData(0);
  }
  else
  {
    searchVinPhone.style.display = "inline";
    let loading_years = jQuery(`.tab.` + steps[0].name);
    loading_years.hide();
    for (const key in selected) {
      if (selected.hasOwnProperty(key)) {
          // Remove "Sel" from the end of the key and assign the value to data_json
          const newKey = key.replace("Sel", "");
          data_json[newKey] = selected[key];
      }
  }
  updateSelectedDataText();
  // Get an array of keys
  const keysArray = Object.keys(data_json);
  // Get the last key (at the end of the array)
  const lastKey = keysArray[keysArray.length - 1];
  currentTab = getIndexByName(lastKey) + 1;
  showTab(currentTab);
  }
});
function submitData()
{
    var formData = new FormData();
    formData.append('action', 'quote');
    formData.append('nonce', ajax_object.nonce);

    Object.keys(data_json).forEach(function (key) {
      if (data_json[key] !== undefined && data_json[key] !== null && data_json[key] !== '') {
        formData.append(key, data_json[key]);
      }
    });

    var vehiclePhotoInput = document.getElementById('vehicle_photos');
    if (vehiclePhotoInput && vehiclePhotoInput.files) {
      for (var i = 0; i < vehiclePhotoInput.files.length; i++) {
        formData.append('vehicle_photos[]', vehiclePhotoInput.files[i]);
      }
    }

    var titleImageInput = document.getElementById('title_image');
    if (titleImageInput && titleImageInput.files && titleImageInput.files[0]) {
      formData.append('title_image', titleImageInput.files[0]);
    }

    var x = document.getElementsByClassName("tab");
    x[currentTab].style.display = "none";
    results.show();
    loading.show();
    result.hide();
    error.hide();
    nextBtn.style.display = "none";
    prevBtn.style.display = "none";
    tryAgainButton.style.display = "inline";
    prevBtnResults.style.display = "inline";
    jQuery.ajax({
      url: ajax_object.ajax_url,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        console.log(response);
          loading.hide();
          document.getElementById('selected_cat').innerHTML = "Result";
          if(response.success == true)
          {
            result.show();
            quote.textContent = "$ " + response.data.proQuote;
          }
          else
          {
            if (response.data && response.data.message) {
              error.text(response.data.message);
            }
            showError();
          }
      },
      error: function(xhr, textStatus, errorThrown) {
          console.error('AJAX Error:', errorThrown);
          showError();
      }
  });
}
function showError()
{
  result.hide();
  loading.hide();
  error.show();
}
function tryAgain()
{
  window.location.reload();
}
function getData(step) {
          let error = document.querySelector(`.error.${steps[step].name}`);
            error.style.display = "none";
  let loading_years = jQuery(`.loading.` + steps[step].name);
  let data_years = jQuery(`.data.` + steps[step].name);
  data_years.hide();
  var data = {
      action: steps[step].name, // AJAX action name
      nonce: ajax_object.nonce // Nonce
  };
  if(steps[step].name == 'model')
  {
    data['make'] = data_json.make;
    data['year'] = data_json.year;
  }
  else if(steps[step].name == 'zip'){
    let inputElement = document.querySelector(`.${steps[step].name} input#${steps[step].name}`);
    data['zip'] = inputElement.value;
  }
  jQuery.ajax({
      url: ajax_object.ajax_url,
      method: 'POST',
      data: data,
      success: function(response) {
        if(steps[step].name != 'zip')
        {
           if (!Array.isArray(response.data.data)) {
            loading_years.hide();
            let error = document.querySelector(`.error.${steps[step].name}`);
            error.style.display = "block";
            // Add your logic here
          } else {
            renderDataList(response.data.data, step);
            loading_years.hide();
            data_years.show();
          }
        }
        else
        {
          let error = document.querySelector(`.error.${steps[step].name}`);
          let inputElement = document.querySelector(`.${steps[step].name} input#${steps[step].name}`);
          if(response.data.data.success == false)
          {
            error.style.display = "block";
            nextBtn.style.display = "none";
            error.textContent = "Invalid Zip code";
          }
          else
          {
            data_json[steps[step].name] = inputElement.value;
            data_json['loc'] = response.data.data.data
            error.style.display = "none";
            nextBtn.style.display = "inline";
          }
        }
        // You can process the response here
      },
      error: function(xhr, textStatus, errorThrown) {
          console.error('AJAX Error:', errorThrown);
          data_years.show();
      }
  });
}

function handleKeyPress(ele) {
  const value = ele.value;
  let id = ele.getAttribute("id");
  if(id == 'zip')
  {
    nextBtn.style.display = "none";
    getData(currentTab);
  }
  data_json[id] = value;
} // Current tab is set to be the first tab (0)
showTab(currentTab); // Display the current tab
function showTab(n) {
  updateSelectedCatText(n)
  tryAgainButton.style.display = "none";
  prevBtnResults.style.display = "none";
  if(n == 4)
  {
    searchVinPhone.style.display = "inline";
  }
  else
  {
    searchVinPhone.style.display = "none";
  }
  // This function will display the specified tab of the form...
  
  var x = document.getElementsByClassName("tab");
  Array.from(x).forEach(function (tab) {
    tab.style.display = "none";
  });
  x[n].style.display = "block";
  //... and fix the Previous/Next buttons:
  if (n == 0) {
    prevBtn.style.display = "none";
  } else {
    prevBtn.style.display = "inline";
  }
  nextBtn.innerHTML = "Next";
  if(steps[n].selectable == true){
    nextBtn.style.display = "none";
  }
  else{
    nextBtn.style.display = "inline";
  }
  if (n == (x.length - 1)) {
    let next = nextBtn;
    next.innerHTML = "Submit";
    nextBtn.style.display = "inline";
  } else {
    nextBtn.innerHTML = "Next";
  }
  steps[n]['selected'] = true;
  //... and run a function that will display the correct step indicator:
}
function prevLast()
{
  results.hide();
  showTab(steps.length - 1);
}
var x = document.getElementsByClassName("tab");
function next(value) {
  updateSelectedDataText();
  var lastIndex = steps.length - 1;
  if (steps.indexOf(steps[currentTab]) == lastIndex) {
    submitData(currentTab);
    return false;
  }
  else
  {
    let inputElement = document.querySelector(`.${steps[currentTab].name} input#${steps[currentTab].name}`);
    let error = document.querySelector(`.error.${steps[currentTab].name}`);

    if (steps[currentTab].name === 'vehicle_photo') {
      var vehiclePhotos = document.getElementById('vehicle_photos');
      var titleImage = document.getElementById('title_image');
      if (!vehiclePhotos || vehiclePhotos.files.length === 0 || !titleImage || titleImage.files.length === 0) {
        if (error) {
          error.style.display = "block";
        }
        return false;
      }
      if (error) {
        error.style.display = "none";
      }
    } else if (inputElement !== null && inputElement.type !== 'file' && (inputElement.offsetWidth > 0 && inputElement.offsetHeight > 0)) {
      if (inputElement.value.trim() === "") {
        if (error) {
          error.style.display = "block";
        }
        return false;
      } else {
        if (error) {
          error.style.display = "none";
        }
        if (steps[currentTab].name == 'zip') {
          getData(currentTab);
        }
      }
    }
    x[currentTab].style.display = "none";   
    // Increase or decrease the current tab by 1:
    currentTab = currentTab + value;
    if(steps[currentTab].getData == true){
      getData(currentTab);
    }
    // if you have reached the end of the form...
    showTab(currentTab);
  }
}
function prev() {
  x[currentTab].style.display = "none";
  let lastIndex = -1; // Initialize with -1 (no object with 'selected' set to true)

  // Loop through the array in reverse order to find the last selected object
  for (let i = steps.length - 1; i >= 0; i--) {
      if (i != currentTab && i < currentTab && steps[i].selected === true) {
          lastIndex = i; // Update the index
          break; // Exit the loop once the last selected object is found
      }
  }
  if(lastIndex == -1)
  {
    currentTab = currentTab -1;
  }
  else
  {
    currentTab = lastIndex;
    results.hide();
  }
  if(!isObjectEmpty(selected))
  {
    steps[currentTab].getData;
    if(steps[currentTab].getData == true){
      getData(currentTab);
    }
  }
  if(vin_selected)
  {
    currentTab = 4;
  }
  showTab(currentTab);
}

function validateForm() {
  // This function deals with validation of the form fields
  var x, y, i, valid = true;
  x = document.getElementsByClassName("tab");
  y = x[currentTab].getElementsByTagName("select");
  // A loop that checks every input field in the current tab:
  for (i = 0; i < y.length; i++) {
    // If a field is empty...
    if (y[i].value == "") {
      // add an "invalid" class to the field:
      y[i].className += " invalid";
      // and set the current valid status to false
      valid = false;
    }
  }
  // If the valid status is true, mark the step as finished and valid:
  if (valid) {
    document.getElementsByClassName("step")[currentTab].className += " finish";
  }
  return valid; // return the valid status
}

function search(step) {
  var input, filter, ul, li, a, i, txtValue;
  input = document.getElementById(steps[step].name + "Input");
  filter = input.value.toLowerCase();
  ul = document.getElementById(steps[step].name);
  li = ul.getElementsByTagName("li");
  for (i = 0; i < li.length; i++) {
      a = li[i].getElementsByTagName("span")[0];
      txtValue = a.textContent || a.innerText;
      if (txtValue.toLowerCase().indexOf(filter) > -1) {
          li[i].style.display = "block";
      } else {
          li[i].style.display = "none";
      }
  }
}
function addEvent(step, ele, value)
{
  var listItems = document.querySelectorAll(`#${steps[step].name} .list-group-item`);
  listItems.forEach(function(item) {
    item.classList.remove('active');
  });
  ele.classList.add('active');
  var lastIndex = steps.length - 1;
  data_json[steps[step].name]= ele.getAttribute('data-value');
  if (steps.indexOf(steps[step]) !== lastIndex) {
    next(value);
  }
  else
  {
    nextBtn.style.display = 'inline';
  }
}
function renderDataList(response, step) {
  var myUL = document.querySelector(`ul#${steps[step].name}`);
  response.forEach(function(data, index) {
      var listItem = document.createElement("li");
      listItem.classList.add('ui-listbox-item');
      var link = document.createElement("span");
      link.classList.add('list-group-item');
      link.href = "#";
      link.textContent = data.value;
      link.setAttribute('data-value', data.key)
      listItem.appendChild(link);
      myUL.appendChild(listItem);
      // Attach click event to each list item
      link.addEventListener("click", function() {
        if(this.getAttribute('data-value').length > 0)
        {
          data_json[steps[step].name]= this.getAttribute('data-value');
        }
        // Get all elements with the class "list-group-item"
        var listItems = document.querySelectorAll(`#${steps[step].name} .list-group-item`);
        // Loop through the list items and remove the "active" class
        listItems.forEach(function(item) {
          item.classList.remove('active');
        });
        listItem.getElementsByTagName('span')[0].classList.add('active');
        var lastIndex = steps.length - 1;
        if (steps.indexOf(steps[step]) !== lastIndex) {
            next(1); // Call nextPrev with the year as the argument
            updateSelectedCatText(step + 1);
        }
        else
        {
          let nextBtn = nextBtn;
          nextBtn.style.display = 'inline';
          updateSelectedDataText();
        }
      });
  });
}

function updateSelectedDataText() {
  const firstThreeValues = Object.values(data_json).slice(0, 4);
  const carString = firstThreeValues.join('-').toUpperCase();
  document.getElementById('selected_data').textContent = carString;
  document.getElementById('selected').textContent = carString;
}
function updateSelectedCatText(index) {
  document.getElementById('selected_cat').innerHTML = steps[index].title;
}
function isObjectEmpty(obj) {
  return Object.keys(obj).length === 0;
}
function getIndexByName(nameToFind) {
  for (let i = 0; i < steps.length; i++) {
      if (steps[i].name === nameToFind) {
          return i; // Return the index if found
      }
  }
  return -1; // Return -1 if not found
}
const searchInput = document.getElementById("searchInput");
const searchButton = document.getElementById("searchButton");
const loading_vin = document.querySelector(".loading-modal");
const errorText = document.querySelector(".error-modal");
// Function to handle the search button click
function handleSearch() {
  const query = searchInput.value.trim();
  if (query === "") {
    errorText.style.display = "block";
    loading_vin.style.display = "none";
  } else {
    errorText.style.display = "none";
    loading_vin.style.display = "block";
    // Perform your search logic here (e.g., send a request to the server).
    // When the search is complete, you can hide the loading bar and show the results.
    let loading_modal = jQuery(`.loading-modal`);
    loading_modal.show();
    var data = {
        action: 'vin', // AJAX action name
        vin: query,
        nonce: ajax_object.nonce // Nonce
    };
    jQuery.ajax({
        url: ajax_object.ajax_url,
        method: 'POST',
        data: data,
        success: function(response) {
          loading_modal.hide();
          if(response.data.success == true)
          {
            data = response.data;
            for (var key in data) {
              if (data.hasOwnProperty(key)) {
                // Add the property to the data_json object
                data_json[key] = data[key];
              }
            }
            searchInput.value = "";
            closeModals();
            updateSelectedDataText();
            vin_selected = true;
            currentTab = 4;
            showTab(currentTab);
          }
          else
          {
            errorText.style.display = "block";
            loading_vin.style.display = "none";
            errorText.textContent = "Invalid vin"
          }
        },
        error: function(xhr, textStatus, errorThrown) {
            loading_modal.hide();
        }
    });
  }
}
searchButton.addEventListener("click", handleSearch);

function bindPhotoPreview(inputId, previewId) {
  var photoInput = document.getElementById(inputId);
  if (!photoInput) {
    return;
  }

  photoInput.addEventListener('change', function () {
    var preview = document.getElementById(previewId);
    if (!preview) {
      return;
    }
    preview.innerHTML = '';
    Array.from(photoInput.files).forEach(function (file) {
      var img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.className = 'photo-preview-thumb';
      preview.appendChild(img);
    });
    data_json[inputId] = photoInput.files.length > 0 ? 'uploaded' : '';
  });
}

bindPhotoPreview('vehicle_photos', 'vehicle_photos_preview');
bindPhotoPreview('title_image', 'title_image_preview');
