const numberInput=document.getElementById( 'numberInput' );
const inputArray=document.getElementById( 'inputArray' );
const sortedArray=document.getElementById( 'sortedArray' );
const addBtn=document.getElementById( 'addBtn' );
const sortBtn=document.getElementById( 'sortBtn' );
const clearBtn=document.getElementById( 'clearBtn' );

let numbers=[];

// Regex validation for number input
numberInput.addEventListener( 'input', function ( e )
{
        if ( /[^0-9]/.test( this.value ) )
        {
                alert( "Please enter numbers only!" );
                this.value=this.value.replace( /[^0-9]/g, '' );
        }
} );

// Add number to array
addBtn.addEventListener( 'click', function ()
{
        const num=parseInt( numberInput.value );
        if ( num )
        {
                numbers.push( num );
                const option=new Option( num, num );
                inputArray.add( option );
                numberInput.value='';
        }
} );

// Bubble sort function
function bubbleSort ( arr )
{
        const n=arr.length;
        for ( let i=0; i<n-1; i++ )
        {
                for ( let j=0; j<n-i-1; j++ )
                {
                        if ( arr[ j ]>arr[ j+1 ] )
                        {
                                // Swap elements
                                let temp=arr[ j ];
                                arr[ j ]=arr[ j+1 ];
                                arr[ j+1 ]=temp;
                        }
                }
        }
        return arr;
}

// Sort array and display result
sortBtn.addEventListener( 'click', function ()
{
        const sortedNumbers=bubbleSort( [ ...numbers ] );
        sortedArray.innerHTML='';
        sortedNumbers.forEach( num =>
        {
                const option=new Option( num, num );
                sortedArray.add( option );
        } );
} );

// Clear functionality
clearBtn.addEventListener( 'click', function ()
{
        numbers=[];
        inputArray.innerHTML='';
        sortedArray.innerHTML='';
        numberInput.value='';
} );