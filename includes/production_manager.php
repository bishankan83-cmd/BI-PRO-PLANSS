<style>
    .custom-card {
        background-color: #f0f0f0;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
        margin-top: 100px;
    }

    .card-title {
        font-size: 24px;
        color: #333;
    }

    .card-description {
        font-size: 16px;
        color: #666;
    }

    .btn-primary {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .card-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px; /* Adjust margin-top to move buttons down */
    }
</style>

<div class="row">
    <div class="col-md-6">
        <div class="custom-card" style="background-image: url('your-image-url.jpg');">
            <div class="card-content">
                <h5 class="card-title">Daily Production</h5>
                <p class="card-description">View Daily Production Data</p>
                <a href="daily_production.php" class="btn btn-primary">Go</a>
                
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="custom-card" style="background-image: url('your-image-url.jpg');">
            <div class="card-content">
                <h5 class="card-title">Add Daily Production</h5>
                <p class="card-description">Add New Daily Production</p>
                <a href="add_production.php" class="btn btn-primary">Go</a>
           
            </div>
        </div>
    </div>
</div>


