<template>

    <!-- the map, will be visible after loading is finished -->
    <div v-if="!isLoading && requestSent" class="animated fadeIn">
        <GmapMap
        :center="{lat:30, lng:30}"
        :zoom="5"
        map-type-id="terrain"
        style="width: 1000px; height: 600px"
        >
            <GmapMarker
                :key="index"
                v-for="(m, index) in markers"
                :position="google && new google.maps.LatLng(m.lat, m.lng)"
                :clickable="true"
                :draggable="false"
                @click="displayFeedMessage(m)"
            />
        </GmapMap>

    </div>

    <!-- spinnenrm will be visible while loading -->
    <div v-else-if="isLoading && requestSent" class="animated fadeIn center-div">
        <semipolar-spinner
        :animation-duration="1000"
        :size="60"
        :color="'#00b894'"
        />
    </div>

    <!-- a button to send the request only after the user clicks on it -->
    <div v-else-if="!requestSent" class="center-div">
        <button @click="fetchFeedsFromServer" class="animated fadeIn green-button">Request New Feed</button>
    </div>

</template>

<script>

import {gmapApi} from 'vue2-google-maps'
import {SemipolarSpinner} from 'epic-spinners'


export default {
    components: {
        SemipolarSpinner
    },
    computed : {
        google: gmapApi
    },
    methods: {
        displayFeedMessage(feed) {
            // displaying the feed message
            this.$swal(feed.body);
        },
        fetchFeedsFromServer() {
            this.isLoading = true
            this.requestSent = true

            // calling the serve-api to fetch data
            axios.get('/api/fetch-feed')
            .then(response => {
                // passing markers to the map
                this.markers = response.data.data
                this.isLoading = false
            })
            .catch(error => {
                // re-enabling the button again
                this.requestSent = false
                this.$swal('Error while fetching data from server', {
                    type: 'error'
                });
            })
        }
    },
    data() {
        return {
            isLoading: true,
            requestSent: false,
            markers: [],
        }
    }
}
</script>

<style scoped>
    .center-div {
        position: absolute;
        top: 50%;
        left: 50%;
        transform:translateX(-50%);
    }

    .green-button {
        background-color: #00b894;
        padding: 10px;
        border: none;
        color: #fff;
        border-radius: 4px;
        cursor: pointer;
    }

</style>
